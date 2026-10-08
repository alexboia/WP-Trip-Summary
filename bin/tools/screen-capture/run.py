from dataclasses import dataclass, field
from getpass import getpass
from pathlib import Path
from tempfile import NamedTemporaryFile
from urllib.parse import urlsplit
from rich.console import Console

import yaml
from playwright.sync_api import sync_playwright, expect
from playwright.sync_api import Browser
from playwright.sync_api import Page

WPTS_TIMEOUT = 10000

WPTS_CONFIG_PATH = Path("./config.yaml")
WPTS_CONFIG_FIELDS = {
	"baseUrl": "WordPress base URL",
	"userName": "WordPress username",
	"password": "WordPress password",
	"knownSamplePostEdit": "Sample post ID for editor screenshots",
	"knownSamplePostView": "Sample post permalink for viewer screenshots"
}

WPTS_ABOUT_URL = "admin.php?page=abp01-trip-summary-about"
WPTS_SETTINGS_URL = "admin.php?page=abp01-trip-summary-settings"
WPTS_MAINTENANCE_URL = "admin.php?page=abp01-trip-summary-maintenance"

WPTS_CONSOLE = Console()

@dataclass
class WptsConfig:
	# Base URL of WordPress instance
	baseUrl: str
	# Username to authenticate with
	userName: str
	# Password to authenticate with
	password: str = field(repr=False)
	# Post ID to use for editor screenshots
	knownSamplePostEdit: str
	# Post permalink to use for viewer screenshots
	knownSamplePostView: str

def wp_admin_url(baseUrl:str):
	return baseUrl.rstrip('/') + "/wp-admin/"

def wp_login_url(baseUrl: str):
	return baseUrl.rstrip('/') + "/wp-login.php"

def wpts_about_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WPTS_ABOUT_URL

def wpts_settings_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WPTS_SETTINGS_URL

def wpts_maintenance_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WPTS_MAINTENANCE_URL

def wpts_post_url(baseUrl: str, page: str):
	return baseUrl.rstrip('/') + '/' + page.lstrip('/')

def logon(browser: Browser, url: str, userName: str, password: str, retries: int) -> Page:
	logonPage = browser.new_page(viewport={
		"width": 1920, 
		"height": 1080
	})

	logonPage.goto(url, timeout=10000, wait_until="domcontentloaded")
	
	logonPage.fill("#user_login", userName)
	logonPage.fill("#user_pass", password)
	logonPage.click("#wp-submit")

	while retries > 0:
		try:
			logonPage.wait_for_url("**/wp-admin**", timeout=WPTS_TIMEOUT, wait_until="domcontentloaded")
			break
		except TimeoutError as timeoutErr:
			retries -= 1
			if (retries == 0):
				raise timeoutErr

	return logonPage

def wpts_about_screenshot(currentPage: Page, url: str, includeFullPage: bool = False) -> Page:
	currentPage.goto(url, wait_until="domcontentloaded")

	currentPage.screenshot(path="./screenshots/wpts-about.png")
	if (includeFullPage):
		currentPage.screenshot(path="./screenshots/wpts-about-full.png", full_page=True)

	return currentPage

def wpts_settings_screenshots(currentPage: Page, url: str, includeFullPage: bool = False) -> Page:
	currentPage.goto(url, timeout=10000, wait_until="domcontentloaded")

	#General settings section
	currentPage.click("#abp01-general-settings-tab")
	currentPage.wait_for_selector("#abp01-general-settings", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(500)

	currentPage.screenshot(path="./screenshots/wpts-settings-general.png")
	if (includeFullPage):
		currentPage.screenshot(path="./screenshots/wpts-settings-general-full.png", full_page=True)

	#Viewer settings section
	currentPage.click("#abp01-viewer-settings-tab")
	currentPage.wait_for_selector("#abp01-viewer-settings", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(500)

	currentPage.screenshot(path="./screenshots/wpts-settings-viewer.png")
	if (includeFullPage):
		currentPage.screenshot(path="./screenshots/wpts-settings-viewer-full.png", full_page=True)

	#Map settings section
	currentPage.click("#abp01-map-settings-tab")
	currentPage.wait_for_selector("#abp01-map-settings", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(500)

	#Make sure API key not visible
	currentPage.fill("#abp01-tileLayerApiKey", "")
	
	currentPage.screenshot(path="./screenshots/wpts-settings-map.png")
	if (includeFullPage):
		currentPage.screenshot(path="./screenshots/wpts-settings-map-full.png", full_page=True)

	#Also screencap the pre-defined tile layers modal
	currentPage.click("#abp01-predefined-tile-layer-selector")
	currentPage.wait_for_selector("#abp01-predefined-tile-layers-window", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(500)

	currentPage.screenshot(path="./screenshots/wpts-settings-map-predefined-layers.png")
	return currentPage

def wpts_maintenance_screenshots(currentPage: Page, url: str, includeFullPage: bool = False) -> Page:
	currentPage.goto(url, timeout=WPTS_TIMEOUT, wait_until="domcontentloaded")

	#Just landed on the page, nothing selected
	currentPage.screenshot(path="./screenshots/wpts-maintenance-default.png")
	if (includeFullPage):
		currentPage.screenshot(path="./screenshots/wpts-maintenance-default-full.png", full_page=True)

	#Sample 1 - Detect missing track files
	wpts_execute_maintenance_tool(currentPage, 
		option="detect-missing-track-files", 
		waitForSelector="#abp01-admin-missing-tracks-posts")

	currentPage.screenshot(path="./screenshots/wpts-maintenance-missing-track-files.png")
	if (includeFullPage):
		currentPage.screenshot(path="./screenshots/wpts-maintenance-missing-track-files-full.png", full_page=True)

	#Sample 2 - Nginx Access Directives Helper
	wpts_execute_maintenance_tool(currentPage, 
		option="nginx-access-directives-helper", 
		waitForSelector="#wpts-nginx-directives-container")

	currentPage.screenshot(path="./screenshots/wpts-maintenance-nginx.png")
	if (includeFullPage):
		currentPage.screenshot(path="./screenshots/wpts-maintenance-nginx-full.png", full_page=True)
	
	return currentPage

def wpts_execute_maintenance_tool(currentPage: Page, option: str, waitForSelector: str):
	currentPage.select_option("#abp01-maintenance-tool-select", option)
	currentPage.click("#abp01-execute-maintenance-tool")

	currentPage.wait_for_selector("#abp01-confirm-dialog-modal", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(500)

	currentPage.click("#abp01-confirm-dialog-modal button[data-abp01-modal-action='yes']")
	currentPage.wait_for_timeout(150)

	currentPage.wait_for_selector(waitForSelector, state="visible", timeout=10000)
	currentPage.wait_for_selector("#abp01-progress-container", state="hidden", timeout=WPTS_TIMEOUT)

	currentPage.wait_for_timeout(150)

def wpts_post_view_screenshot(currentPage: Page, url: str, includeFullPage: bool = False) -> Page:
	currentPage.goto(url, timeout=10000, wait_until="domcontentloaded")

	currentPage.wait_for_selector("#abp01-techbox-teaser", timeout=WPTS_TIMEOUT)
	teaser = currentPage.locator("#abp01-techbox-teaser")
	teaser.screenshot(path="./screenshots/wpts-sample-post-teaser.png")

	currentPage.click("#abp01-techbox-teaser-action")
	currentPage.wait_for_timeout(150)

	viewer = currentPage.locator("#abp01-techbox-frontend")
	expect(viewer).to_be_in_viewport()

	currentPage.click("#abp01-tab-info a")
	currentPage.wait_for_selector("#abp01-techbox-info", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(150)
	viewer.screenshot(path="./screenshots/wpts-sample-post-viewer-info.png")

	currentPage.click("#abp01-tab-map a")
	currentPage.wait_for_selector("#abp01-techbox-map", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_selector("#abp01-map .leaflet-tile", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(150)
	viewer.screenshot(path="./screenshots/wpts-sample-post-viewer-map.png")

	map = currentPage.locator("#abp01-map")
	profileButton = map.locator(".abp01-map-altitude-profile-btn >> ..")
	profileButton.click()

	currentPage.wait_for_selector("#abp01-altitude-profile-container", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(150)
	viewer.screenshot(path="./screenshots/wpts-sample-post-viewer-map-alt-profile.png")

	currentPage.click("#abp01-route-log a")
	currentPage.wait_for_selector("#abp01-route-log-content", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(150)
	viewer.screenshot(path="./screenshots/wpts-sample-post-viewer-log.png")

	if (includeFullPage):
		currentPage.screenshot(path="./screenshots/wpts-sample-post-full.png")

	return currentPage

def _wpts_config_value(name: str, value) -> str:
	if name == "knownSamplePostEdit" and type(value) is int:
		value = str(value)
	if not isinstance(value, str) or not value.strip():
		raise ValueError(f"{name} must be a non-empty string.")

	if name != "password":
		value = value.strip()

	if name == "baseUrl":
		try:
			url = urlsplit(value)
			valid = url.scheme in ("http", "https") and bool(url.hostname)
			valid = valid and not any(character.isspace() for character in value)
			valid = valid and not url.query and not url.fragment
			valid = valid and url.username is None and url.password is None
			_ = url.port
		except ValueError:
			valid = False
		if not valid:
			raise ValueError("baseUrl must be an HTTP(S) URL without credentials, a query or a fragment.")
	elif name == "knownSamplePostEdit":
		if not value.isascii() or not value.isdecimal() or int(value) <= 0:
			raise ValueError("knownSamplePostEdit must be a positive post ID.")
	elif name == "knownSamplePostView":
		try:
			url = urlsplit(value)
			valid = not url.scheme and not url.netloc and bool(url.path or url.query)
		except ValueError:
			valid = False
		if not valid:
			raise ValueError("knownSamplePostView must be a permalink relative to the WordPress base URL.")

	return value


def wpts_setup(force: bool):
	"""Reuse a valid config, or interactively collect and save all required values."""
	try:
		currentConfig = wpts_config()
	except (FileNotFoundError, ValueError):
		currentConfig = None

	if currentConfig is not None and not force:
		return

	configData = {}
	for name, label in WPTS_CONFIG_FIELDS.items():
		currentValue = getattr(currentConfig, name) if currentConfig is not None else None
		if currentValue is None:
			prompt = f"{label}: "
		elif name == "password":
			prompt = f"{label} [Enter to keep the current password]: "
		else:
			prompt = f"{label} [{currentValue}]: "

		while True:
			value = getpass(prompt) if name == "password" else input(prompt)
			if value == "" and currentValue is not None:
				value = currentValue
			try:
				configData[name] = _wpts_config_value(name, value)
				break
			except ValueError as error:
				print(error)

	# Replace only after every value is valid and the complete YAML has been written.
	temporaryPath = None
	try:
		with NamedTemporaryFile(mode="w", encoding="utf-8", newline="\n",
			dir=WPTS_CONFIG_PATH.parent, prefix=".wpts-config-", suffix=".tmp", delete=False) as configFile:
			temporaryPath = Path(configFile.name)
			yaml.safe_dump(configData, configFile, allow_unicode=True, sort_keys=False)
		temporaryPath.replace(WPTS_CONFIG_PATH)
	finally:
		if temporaryPath is not None:
			temporaryPath.unlink(missing_ok=True)


def wpts_config() -> WptsConfig:
	"""Read and validate ./config.yaml without prompting or starting a browser."""
	try:
		with WPTS_CONFIG_PATH.open(encoding="utf-8") as configFile:
			configData = yaml.safe_load(configFile)
	except (yaml.YAMLError, UnicodeError):
		# Parser messages may include configuration contents, including the password.
		raise ValueError("config.yaml must contain valid UTF-8 YAML.") from None

	if not isinstance(configData, dict):
		raise ValueError("config.yaml must contain a mapping of configuration fields.")

	return WptsConfig(**{
		name: _wpts_config_value(name, configData.get(name))
		for name in WPTS_CONFIG_FIELDS
	})


def main():
	try:
		wpts_setup(force=False)
		config = wpts_config()
	except (OSError, ValueError, EOFError) as error:
		raise SystemExit(f"Cannot configure screen capture: {error}") from None
	except KeyboardInterrupt:
		raise SystemExit("Screen capture setup cancelled.") from None

	with sync_playwright() as playwright:
		browser = playwright.firefox.launch(headless=True)
		logonUrl = wp_login_url(config.baseUrl)

		try:
			WPTS_CONSOLE.print(f'Logging on to {config.baseUrl}...', style="bold yellow3")
			wpAdmin = logon(browser, logonUrl,
				userName=config.userName,
				password=config.password,
				retries=3)

			WPTS_CONSOLE.print(':thumbs_up: Successfully logged on', style="bold green")
		except TimeoutError:
			WPTS_CONSOLE.print("Logon timed out :pile_of_poo:!", style="bold red")
			raise SystemExit(1)

		with WPTS_CONSOLE.status("[bold green] Capturing screenshots...") as status:
			wptsAbout = wpts_about_screenshot(wpAdmin, wpts_about_url(config.baseUrl))
			WPTS_CONSOLE.log(":thumbs_up: Captured Admin About page!", style="bold green")

			wptsSettings = wpts_settings_screenshots(wptsAbout, wpts_settings_url(config.baseUrl))
			WPTS_CONSOLE.log(":thumbs_up: Captured Admin Settings page!", style="bold green")

			wptsMaintenance = wpts_maintenance_screenshots(wptsSettings, wpts_maintenance_url(config.baseUrl))
			WPTS_CONSOLE.log(":thumbs_up: Captured Admin Maintenance page!", style="bold green")

			wptsPostSample = wpts_post_view_screenshot(wptsMaintenance, wpts_post_url(config.baseUrl, config.knownSamplePostView))
			WPTS_CONSOLE.log(":thumbs_up: Captured Frontend Sample page!", style="bold green")

		browser.close()


if __name__ == "__main__":
	main()
