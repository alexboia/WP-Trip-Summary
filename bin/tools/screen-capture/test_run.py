import contextlib
import io
import sys
import tempfile
import unittest
from dataclasses import asdict
from pathlib import Path
from unittest.mock import call, patch

import yaml

import config as configuration
import context as capture_context
import run
from data import WptsContext, WptsScreenshot


class WptsConfigTests(unittest.TestCase):

	def setUp(self):
		self.temporaryDirectory = tempfile.TemporaryDirectory()
		self.addCleanup(self.temporaryDirectory.cleanup)
		self.configPath = Path(self.temporaryDirectory.name) / "config.yaml"
		self.pathPatch = patch.object(configuration, "WPTS_CONFIG_PATH", self.configPath)
		self.pathPatch.start()
		self.addCleanup(self.pathPatch.stop)
		self.values = {
			"baseUrl": "https://example.test/wordpress/",
			"userName": "sample-user",
			"password": "  sample:#password  ",
			"knownSamplePostEdit": "42",
			"knownSamplePostView": "/2026/10/traseu/"
		}

	def write_config(self, values=None):
		self.configPath.write_text(yaml.safe_dump(self.values if values is None else values), encoding="utf-8")

	def test_config_loads_all_fields_and_preserves_password(self):
		self.write_config()
		config = configuration.wpts_get_config()
		self.assertEqual(asdict(config), self.values)
		self.assertNotIn(self.values["password"], repr(config))

	def test_config_accepts_numeric_post_id_and_unicode(self):
		values = dict(self.values, knownSamplePostEdit=42, userName="călător")
		self.write_config(values)
		config = configuration.wpts_get_config()
		self.assertEqual(config.knownSamplePostEdit, "42")
		self.assertEqual(config.userName, "călător")

	def test_config_missing_file_raises_without_creating_it(self):
		with self.assertRaises(FileNotFoundError):
			configuration.wpts_get_config()
		self.assertFalse(self.configPath.exists())

	def test_config_rejects_empty_or_non_mapping_yaml(self):
		for text in ("", "null", "[]", "42", "true", "a string"):
			with self.subTest(text=text):
				self.configPath.write_text(text, encoding="utf-8")
				with self.assertRaises(ValueError):
					configuration.wpts_get_config()

	def test_config_requires_every_field(self):
		for name in self.values:
			with self.subTest(name=name):
				values = dict(self.values)
				del values[name]
				self.write_config(values)
				with self.assertRaisesRegex(ValueError, name):
					configuration.wpts_get_config()

	def test_config_rejects_invalid_values(self):
		invalidValues = {
			"baseUrl": ["", "ftp://example.test", "example.test", "https://", "https://[bad", "https://example.test:bad", "https://bad host", "https://example.test/?x=1", "https://example.test/#part", "https://user:pass@example.test"],
			"userName": [" ", None, True, 23, []],
			"password": [" ", None, True, 23, {}],
			"knownSamplePostEdit": ["0", -1, True, 1.5, "4.2", "forty-two", "²"],
			"knownSamplePostView": ["", "https://other.test/post/", "//other.test/post/", "#fragment"]
		}
		for name, values in invalidValues.items():
			for value in values:
				with self.subTest(name=name, value=value):
					self.write_config(dict(self.values, **{name: value}))
					with self.assertRaisesRegex(ValueError, name):
						configuration.wpts_get_config()

	def test_config_accepts_relative_and_query_permalinks(self):
		for permalink in ("2026/10/traseu/", "/2026/10/traseu/", "?p=42"):
			with self.subTest(permalink=permalink):
				self.write_config(dict(self.values, knownSamplePostView=permalink))
				self.assertEqual(configuration.wpts_get_config().knownSamplePostView, permalink)

	def test_config_rejects_malformed_yaml_and_unsafe_tags_without_echoing_contents(self):
		for text in ("password: [sample-secret", "!!python/object/apply:builtins.print [sample-secret]"):
			with self.subTest(text=text), contextlib.redirect_stdout(io.StringIO()) as output:
				self.configPath.write_text(text, encoding="utf-8")
				with self.assertRaises(ValueError) as error:
					configuration.wpts_get_config()
				self.assertNotIn("sample-secret", str(error.exception))
				self.assertEqual(output.getvalue(), "")

	def test_config_rejects_non_utf8_input(self):
		self.configPath.write_bytes(b"password: \xff")
		with self.assertRaisesRegex(ValueError, "UTF-8"):
			configuration.wpts_get_config()

	def test_setup_reuses_valid_file_without_prompting_or_rewriting(self):
		self.write_config()
		before = self.configPath.read_bytes()
		with patch("builtins.input") as prompt, patch.object(configuration, "getpass") as passwordPrompt:
			configuration.wpts_setup(force=False)
		prompt.assert_not_called()
		passwordPrompt.assert_not_called()
		self.assertEqual(self.configPath.read_bytes(), before)

	def test_setup_creates_config_and_retries_invalid_answers(self):
		answers = ["", "ftp://example.test", self.values["baseUrl"], "", self.values["userName"], "0", "42", "https://other.test/post", self.values["knownSamplePostView"]]
		with patch("builtins.input", side_effect=answers), patch.object(configuration, "getpass", side_effect=["", self.values["password"]]) as passwordPrompt, contextlib.redirect_stdout(io.StringIO()) as output:
			configuration.wpts_setup(force=False)
		self.assertEqual(asdict(configuration.wpts_get_config()), self.values)
		self.assertEqual(passwordPrompt.call_count, 2)
		self.assertNotIn(self.values["password"], output.getvalue())
		self.assertEqual(list(self.configPath.parent.glob(".wpts-config-*.tmp")), [])

	def test_setup_replaces_invalid_config(self):
		for text in ("not: [valid yaml", "[]", "baseUrl: https://example.test"):
			with self.subTest(text=text):
				self.configPath.write_text(text, encoding="utf-8")
				answers = [self.values[name] for name in self.values if name != "password"]
				with patch("builtins.input", side_effect=answers), patch.object(configuration, "getpass", return_value=self.values["password"]):
					configuration.wpts_setup(force=False)
				self.assertEqual(asdict(configuration.wpts_get_config()), self.values)

	def test_forced_setup_prompts_and_keeps_defaults_without_displaying_password(self):
		self.write_config()
		with patch("builtins.input", return_value="") as prompt, patch.object(configuration, "getpass", return_value="") as passwordPrompt:
			configuration.wpts_setup(force=True)
		self.assertEqual(prompt.call_count, 4)
		passwordPrompt.assert_called_once()
		self.assertNotIn(self.values["password"], passwordPrompt.call_args.args[0])
		self.assertEqual(asdict(configuration.wpts_get_config()), self.values)

	def test_forced_setup_saves_changed_values(self):
		self.write_config()
		with patch("builtins.input", side_effect=["https://new.test", "new-user", "123", "?p=123"]), patch.object(configuration, "getpass", return_value="new-password"):
			configuration.wpts_setup(force=True)
		self.assertEqual(asdict(configuration.wpts_get_config()), {
			"baseUrl": "https://new.test",
			"userName": "new-user",
			"password": "new-password",
			"knownSamplePostEdit": "123",
			"knownSamplePostView": "?p=123"
		})

	def test_cancelled_setup_preserves_existing_file(self):
		self.write_config()
		before = self.configPath.read_bytes()
		for interruption in (EOFError, KeyboardInterrupt):
			with self.subTest(interruption=interruption), patch("builtins.input", side_effect=["https://new.test", interruption]):
				with self.assertRaises(interruption):
					configuration.wpts_setup(force=True)
				self.assertEqual(self.configPath.read_bytes(), before)

	def test_failed_save_preserves_existing_file_and_cleans_temporary_file(self):
		self.write_config()
		before = self.configPath.read_bytes()
		with patch("builtins.input", return_value=""), patch.object(configuration, "getpass", return_value=""), patch.object(Path, "replace", side_effect=PermissionError("Cannot replace config")):
			with self.assertRaises(PermissionError):
				configuration.wpts_setup(force=True)
		self.assertEqual(self.configPath.read_bytes(), before)
		self.assertEqual(list(self.configPath.parent.glob(".wpts-config-*.tmp")), [])

	def test_unreadable_config_does_not_prompt_or_overwrite(self):
		with patch.object(Path, "open", side_effect=PermissionError("Cannot read config")), patch("builtins.input") as prompt:
			with self.assertRaises(PermissionError):
				configuration.wpts_setup(force=False)
		prompt.assert_not_called()

	def test_main_uses_config_for_login_and_capture_urls(self):
		self.write_config()
		for enabled in (False, True):
			arguments = ["--full-pages", "--verbose", "--report", "--viewport", "1280x720"] if enabled else []
			with self.subTest(optionsEnabled=enabled), self._mock_capture_dependencies(arguments) as (mocks, captureContext):
				run.main()
				playwright = mocks["sync_playwright"].return_value.__enter__.return_value
				playwright.firefox.launch.assert_called_once_with(headless=True)
				browser = playwright.firefox.launch.return_value
				logon = mocks["wp_logon"]
				logon.assert_called_once_with(browser, "https://example.test/wordpress/wp-login.php",
					userName=self.values["userName"], password=self.values["password"], retries=3, verbose=enabled)

				self.assertIsNotNone(captureContext.config)
				if captureContext.config is not None:
					self.assertEqual(asdict(captureContext.config), self.values)

				self.assertEqual(captureContext.outDir, self._capture_output_dir())
				self.assertEqual((captureContext.viewPortWidth, captureContext.viewPortHeight),
					(1280, 720) if enabled else (1920, 1080))
				
				stages = (
					("wpts_about_screenshot", "wp-admin/admin.php?page=abp01-trip-summary-about", {}),
					("wpts_settings_screenshots", "wp-admin/admin.php?page=abp01-trip-summary-settings", {}),
					("wpts_maintenance_screenshots", "wp-admin/admin.php?page=abp01-trip-summary-maintenance", {}),
					("wpts_system_logs_screenshot", "wp-admin/admin.php?page=abp01-system-logs", {}),
					("wpts_lookup_data_screenshot", "wp-admin/admin.php?page=abp01-trip-summary-lookup", {}),
					("wpts_post_listing_screenshot", "wp-admin/edit.php", {"postId": "42"}),
					("wpts_post_edit_screenshot", "wp-admin/post.php?post=42&action=edit", {"postId": "42"}),
					("wpts_post_view_screenshot", "2026/10/traseu/", {})
				)
				currentPage = logon.return_value
				
				for name, relativeUrl, options in stages:
					mocks[name].assert_called_once_with(currentPage, self.values["baseUrl"] + relativeUrl,
						includeFullPage=enabled, **options)
					currentPage = mocks[name].return_value

				self.assertEqual(mocks["wp_change_language_to"].call_args_list, [
					call(logon.return_value, "https://example.test/wordpress/wp-admin/options-general.php", "ro_RO"),
					call(currentPage, "https://example.test/wordpress/wp-admin/options-general.php", "de_DE")
				])
				currentPage.close.assert_called_once_with()
				screenshots = mocks["wpts_get_registered_screenshots"].return_value
				mocks["wpts_apply_image_filers"].assert_called_once_with(screenshots, self._capture_output_dir() + "/filtered")
				mocks["wpts_save_screenshot_registry"].assert_called_once_with(screenshots, self._capture_output_dir())
				if enabled:
					mocks["wpts_report_screenshot_registry"].assert_called_once_with(screenshots)
				else:
					mocks["wpts_report_screenshot_registry"].assert_not_called()
				browser.close.assert_called_once_with()

	def test_main_rejects_invalid_arguments_before_starting_browser(self):
		self.write_config()
		before = self.configPath.read_bytes()
		with self._mock_capture_dependencies(["--viewport", "0x720"]) as (mocks, captureContext):
			with self.assertRaisesRegex(SystemExit, "Cannot configure screen capture: --viewport"):
				run.main()
			mocks["sync_playwright"].assert_not_called()
			mocks["wp_logon"].assert_not_called()
			self.assertIsNone(captureContext.config)
		self.assertEqual(self.configPath.read_bytes(), before)

	def test_main_closes_browser_when_capture_fails(self):
		self.write_config()
		with self._mock_capture_dependencies() as (mocks, captureContext):
			mocks["wpts_settings_screenshots"].side_effect = RuntimeError("Capture failed")
			with self.assertRaisesRegex(RuntimeError, "Capture failed"):
				run.main()
			browser = mocks["sync_playwright"].return_value.__enter__.return_value.firefox.launch.return_value
			browser.close.assert_called_once_with()
			mocks["wpts_maintenance_screenshots"].assert_not_called()
			mocks["wpts_apply_image_filers"].assert_not_called()
			mocks["wpts_save_screenshot_registry"].assert_not_called()

	def _capture_output_dir(self):
		return (Path(self.temporaryDirectory.name) / "screenshots").as_posix()

	@contextlib.contextmanager
	def _mock_capture_dependencies(self, arguments=()):
		with contextlib.ExitStack() as patches:
			captureContext = WptsContext(langCode="ro_RO")
			patches.enter_context(patch.object(capture_context, "WPTS_CONTEXT", captureContext))
			patches.enter_context(patch.object(sys, "argv", ["run.py", "--out-dir", self._capture_output_dir(), *arguments]))
			mocks = {
				name: patches.enter_context(patch.object(run, name, autospec=True))
				for name in (
					"sync_playwright", "wp_logon", "wp_change_language_to",
					"wpts_about_screenshot", "wpts_settings_screenshots", "wpts_maintenance_screenshots",
					"wpts_system_logs_screenshot", "wpts_lookup_data_screenshot", "wpts_post_listing_screenshot",
					"wpts_post_edit_screenshot", "wpts_post_view_screenshot", "wpts_apply_image_filers",
					"wpts_get_registered_screenshots", "wpts_save_screenshot_registry", "wpts_report_screenshot_registry",
					"wpts_begin_status", "wpts_report_status_task", "wpts_print_neutral", "wpts_print_ok", "wpts_print_fail"
				)
			}
			mocks["wp_change_language_to"].side_effect = ["de_DE", "ro_RO"]
			mocks["wpts_get_registered_screenshots"].return_value = [WptsScreenshot(
				page="Frontend - View Post", url="2026/10/traseu/", outFile=self._capture_output_dir() + "/sample.png"
			)]
			yield mocks, captureContext


if __name__ == "__main__":
	unittest.main()
