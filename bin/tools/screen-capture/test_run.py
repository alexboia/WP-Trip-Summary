import contextlib
import io
import tempfile
import unittest
from dataclasses import asdict
from pathlib import Path
from unittest.mock import patch

import yaml

import run


class WptsConfigTests(unittest.TestCase):

	def setUp(self):
		self.temporaryDirectory = tempfile.TemporaryDirectory()
		self.addCleanup(self.temporaryDirectory.cleanup)
		self.configPath = Path(self.temporaryDirectory.name) / "config.yaml"
		self.pathPatch = patch.object(run, "WPTS_CONFIG_PATH", self.configPath)
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
		config = run.wpts_get_config()
		self.assertEqual(asdict(config), self.values)
		self.assertNotIn(self.values["password"], repr(config))

	def test_config_accepts_numeric_post_id_and_unicode(self):
		values = dict(self.values, knownSamplePostEdit=42, userName="călător")
		self.write_config(values)
		config = run.wpts_get_config()
		self.assertEqual(config.knownSamplePostEdit, "42")
		self.assertEqual(config.userName, "călător")

	def test_config_missing_file_raises_without_creating_it(self):
		with self.assertRaises(FileNotFoundError):
			run.wpts_get_config()
		self.assertFalse(self.configPath.exists())

	def test_config_rejects_empty_or_non_mapping_yaml(self):
		for text in ("", "null", "[]", "42", "true", "a string"):
			with self.subTest(text=text):
				self.configPath.write_text(text, encoding="utf-8")
				with self.assertRaises(ValueError):
					run.wpts_get_config()

	def test_config_requires_every_field(self):
		for name in self.values:
			with self.subTest(name=name):
				values = dict(self.values)
				del values[name]
				self.write_config(values)
				with self.assertRaisesRegex(ValueError, name):
					run.wpts_get_config()

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
						run.wpts_get_config()

	def test_config_accepts_relative_and_query_permalinks(self):
		for permalink in ("2026/10/traseu/", "/2026/10/traseu/", "?p=42"):
			with self.subTest(permalink=permalink):
				self.write_config(dict(self.values, knownSamplePostView=permalink))
				self.assertEqual(run.wpts_get_config().knownSamplePostView, permalink)

	def test_config_rejects_malformed_yaml_and_unsafe_tags_without_echoing_contents(self):
		for text in ("password: [sample-secret", "!!python/object/apply:builtins.print [sample-secret]"):
			with self.subTest(text=text), contextlib.redirect_stdout(io.StringIO()) as output:
				self.configPath.write_text(text, encoding="utf-8")
				with self.assertRaises(ValueError) as error:
					run.wpts_get_config()
				self.assertNotIn("sample-secret", str(error.exception))
				self.assertEqual(output.getvalue(), "")

	def test_config_rejects_non_utf8_input(self):
		self.configPath.write_bytes(b"password: \xff")
		with self.assertRaisesRegex(ValueError, "UTF-8"):
			run.wpts_get_config()

	def test_setup_reuses_valid_file_without_prompting_or_rewriting(self):
		self.write_config()
		before = self.configPath.read_bytes()
		with patch("builtins.input") as prompt, patch.object(run, "getpass") as passwordPrompt:
			run.wpts_setup(force=False)
		prompt.assert_not_called()
		passwordPrompt.assert_not_called()
		self.assertEqual(self.configPath.read_bytes(), before)

	def test_setup_creates_config_and_retries_invalid_answers(self):
		answers = ["", "ftp://example.test", self.values["baseUrl"], "", self.values["userName"], "0", "42", "https://other.test/post", self.values["knownSamplePostView"]]
		with patch("builtins.input", side_effect=answers), patch.object(run, "getpass", side_effect=["", self.values["password"]]) as passwordPrompt, contextlib.redirect_stdout(io.StringIO()) as output:
			run.wpts_setup(force=False)
		self.assertEqual(asdict(run.wpts_get_config()), self.values)
		self.assertEqual(passwordPrompt.call_count, 2)
		self.assertNotIn(self.values["password"], output.getvalue())
		self.assertEqual(list(self.configPath.parent.glob(".wpts-config-*.tmp")), [])

	def test_setup_replaces_invalid_config(self):
		for text in ("not: [valid yaml", "[]", "baseUrl: https://example.test"):
			with self.subTest(text=text):
				self.configPath.write_text(text, encoding="utf-8")
				answers = [self.values[name] for name in self.values if name != "password"]
				with patch("builtins.input", side_effect=answers), patch.object(run, "getpass", return_value=self.values["password"]):
					run.wpts_setup(force=False)
				self.assertEqual(asdict(run.wpts_get_config()), self.values)

	def test_forced_setup_prompts_and_keeps_defaults_without_displaying_password(self):
		self.write_config()
		with patch("builtins.input", return_value="") as prompt, patch.object(run, "getpass", return_value="") as passwordPrompt:
			run.wpts_setup(force=True)
		self.assertEqual(prompt.call_count, 4)
		passwordPrompt.assert_called_once()
		self.assertNotIn(self.values["password"], passwordPrompt.call_args.args[0])
		self.assertEqual(asdict(run.wpts_get_config()), self.values)

	def test_forced_setup_saves_changed_values(self):
		self.write_config()
		with patch("builtins.input", side_effect=["https://new.test", "new-user", "123", "?p=123"]), patch.object(run, "getpass", return_value="new-password"):
			run.wpts_setup(force=True)
		self.assertEqual(asdict(run.wpts_get_config()), {
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
					run.wpts_setup(force=True)
				self.assertEqual(self.configPath.read_bytes(), before)

	def test_failed_save_preserves_existing_file_and_cleans_temporary_file(self):
		self.write_config()
		before = self.configPath.read_bytes()
		with patch("builtins.input", return_value=""), patch.object(run, "getpass", return_value=""), patch.object(Path, "replace", side_effect=PermissionError("Cannot replace config")):
			with self.assertRaises(PermissionError):
				run.wpts_setup(force=True)
		self.assertEqual(self.configPath.read_bytes(), before)
		self.assertEqual(list(self.configPath.parent.glob(".wpts-config-*.tmp")), [])

	def test_unreadable_config_does_not_prompt_or_overwrite(self):
		with patch.object(Path, "open", side_effect=PermissionError("Cannot read config")), patch("builtins.input") as prompt:
			with self.assertRaises(PermissionError):
				run.wpts_setup(force=False)
		prompt.assert_not_called()

	def test_main_uses_config_for_login_and_capture_urls(self):
		self.write_config()
		with patch.object(run, "sync_playwright") as playwright, patch.object(run, "logon") as logon, patch.object(run, "wpts_about_screenshot") as about, patch.object(run, "wpts_settings_screenshots") as settings, patch.object(run, "wpts_maintenance_screenshots") as maintenance, patch.object(run, "wpts_post_view_screenshot") as viewer:
			run.main()
			browser = playwright.return_value.__enter__.return_value.firefox.launch.return_value
			logon.assert_called_once_with(browser, "https://example.test/wordpress/wp-login.php", userName=self.values["userName"], password=self.values["password"], retries=3)
			about.assert_called_once_with(logon.return_value, run.wpts_about_url(self.values["baseUrl"]))
			settings.assert_called_once_with(about.return_value, run.wpts_settings_url(self.values["baseUrl"]))
			maintenance.assert_called_once_with(settings.return_value, run.wpts_maintenance_url(self.values["baseUrl"]))
			viewer.assert_called_once_with(maintenance.return_value, "https://example.test/wordpress/2026/10/traseu/")


if __name__ == "__main__":
	unittest.main()
