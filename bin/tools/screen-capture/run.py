from dataclasses import dataclass, field, asdict
from getpass import getpass
from pathlib import Path
from tempfile import NamedTemporaryFile
from urllib.parse import urlsplit
from rich.console import Console
from rich.status import Status

import yaml
from playwright.sync_api import sync_playwright, expect
from playwright.sync_api import Browser
from playwright.sync_api import Page, Locator
from playwright.sync_api import TimeoutError

import cv2
from cv2.typing import MatLike
import numpy as np
import os
import json

@dataclass
class WptsArgs:
	# Use this host, --host
	host: str
	# Use this username for logon, --username
	userName: str
	# Use this password for logon, --password
	password: str
	# Where to save screenshots, --output-dir
	outDir: str = "./screenshots"
	# Resolution to use, WIDTHxHEIGHT format, --viewport
	viewport: str = "1920x1080"
	# Override host, username and password in current configuration, --save-config
	saveCofig: bool = False
	# Restart setup process, will use any given values that overlap as defaults, --reconfigure
	reconfigure: bool = False
	# Whether to include full pages or not, --full-pages
	includeFullPages: bool = False
	# Show help, --help
	showHelp: bool = False

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

@dataclass
class WptsScreenshot:
	# Human readable description of the page
	page: str
	# Url from which the capture has been generated
	url: str
	# Full path of resulting file
	outFile: str
	# Whether or not is was a full page screenshot
	isFullPage: bool = False
	# If only an element has been captured, describe it here
	element: str|None = None

@dataclass
class WptsContext:
	config: WptsConfig|None = None
	viewPortWidth: int = 1920
	viewPortHeight: int = 1080
	outDir: str = "./screenshots"
	# Empty means US English
	langCode: str = ""

WPTS_TIMEOUT = 10000

WPTS_CONFIG_PATH = Path("./config.yaml")
WPTS_CONFIG_FIELDS = {
	"baseUrl": "WordPress base URL",
	"userName": "WordPress username",
	"password": "WordPress password",
	"knownSamplePostEdit": "Sample post ID for editor screenshots",
	"knownSamplePostView": "Sample post permalink for viewer screenshots"
}

WP_OPTIONS_GENERAL_URL = "options-general.php"

WPTS_ABOUT_URL = "admin.php?page=abp01-trip-summary-about"
WPTS_SETTINGS_URL = "admin.php?page=abp01-trip-summary-settings"
WPTS_MAINTENANCE_URL = "admin.php?page=abp01-trip-summary-maintenance"
WPTS_SYSTEM_LOGS_URL = "admin.php?page=abp01-system-logs"
WPTS_LOOKUP_DATA_URL = "admin.php?page=abp01-trip-summary-lookup"

WPTS_CONSOLE = Console()
WPTS_SCREENSHOT_REGISTRY: list[WptsScreenshot] = []
WPTS_CONTEXT = WptsContext()

def wp_admin_url(baseUrl:str):
	return baseUrl.rstrip('/') + "/wp-admin/"

def wp_login_url(baseUrl: str):
	return baseUrl.rstrip('/') + "/wp-login.php"

def wp_options_general_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WP_OPTIONS_GENERAL_URL

def wpts_about_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WPTS_ABOUT_URL

def wpts_settings_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WPTS_SETTINGS_URL

def wpts_maintenance_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WPTS_MAINTENANCE_URL

def wpts_admin_system_logs_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WPTS_SYSTEM_LOGS_URL

def wpts_lookup_data_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WPTS_LOOKUP_DATA_URL

def wpts_post_url(baseUrl: str, page: str):
	return baseUrl.rstrip('/') + '/' + page.lstrip('/')

def wpts_goto(currentPage: Page, url: str):
	currentPage.goto(url=url, 
		timeout=WPTS_TIMEOUT, 
		wait_until="domcontentloaded")

def wpts_screenshot_page(currentPage: Page, outFile: str, fullPage: bool|None = None) -> str:
	outPath = f'{WPTS_CONTEXT.outDir}/{outFile}'
	currentPage.screenshot(path=outPath, full_page=fullPage)
	return outPath

def wpts_screenshot_locator(locator: Locator, outFile: str):
	outPath = f'{WPTS_CONTEXT.outDir}/{outFile}'
	locator.screenshot(path=outPath)
	return outPath

def wpts_to_relative_url(url: str) -> str:
	return url.replace(WPTS_CONTEXT.config.baseUrl, "").lstrip('/') \
		if WPTS_CONTEXT.config is not None \
		else url

def wpts_register_screenshot(screnshot: WptsScreenshot):
	WPTS_SCREENSHOT_REGISTRY.append(WptsScreenshot(
		outFile = screnshot.outFile,
		url = wpts_to_relative_url(screnshot.url),
		page = screnshot.page,
		element =screnshot.element,
		isFullPage =screnshot.isFullPage
	))

def wpts_get_registered_screenshots() -> list[WptsScreenshot]:
	return WPTS_SCREENSHOT_REGISTRY

def wpts_apply_image_filers(screenshots: list[WptsScreenshot], filteredOutDir: str):
	filteredOutDir = filteredOutDir.rstrip('/')
	if not os.path.isdir(filteredOutDir):
		os.mkdir(filteredOutDir, 777)

	for screen in screenshots:
		inputImage = cv2.imread(screen.outFile)
		if inputImage is not None:
			outputImage = wpts_apply_soften_filter(inputImage, diameter=7, sigmaSpace=110)
			outputImage = wpts_apply_vignette_filter(outputImage)
			fileName = os.path.basename(screen.outFile)

			cv2.imwrite(f'{filteredOutDir}/{fileName}', outputImage)
		else:
			WPTS_CONSOLE.print(f':pile_of_poo: Could not open mage {screen.outFile}', style="bold red")

def wpts_apply_soften_filter(inputImage: MatLike, diameter=12, sigmaSpace=100) -> MatLike:
	outputImage = cv2.bilateralFilter(inputImage, d=diameter, sigmaColor=75, sigmaSpace=sigmaSpace)
	return outputImage

def wpts_apply_vignette_filter(inputImage: MatLike, size:float=0.65) -> MatLike:
	rows, cols = inputImage.shape[:2]

	if (rows < 200 or cols < 200):
		return inputImage

	sigmaX = cols * size
	sigmaY = rows * size

	xResultantKernel = cv2.getGaussianKernel(cols, sigmaX)
	yResultantKernel = cv2.getGaussianKernel(rows, sigmaY)

	mask = np.outer(yResultantKernel, xResultantKernel)
	maskNormalized = mask / mask.max()
	
	outputImage = np.zeros_like(inputImage, dtype=np.uint8)
	for i in range(3):
		outputImage[:, :, i] = inputImage[:, :, i] * maskNormalized

	return outputImage

def wpts_fill_field(currentPage: Page, selector: str, value: str, retries: int = 3) -> bool:
	currentPage.wait_for_selector(selector, state="visible")
	currentPage.fill(selector, value)

	currentPage.wait_for_timeout(50)
	readValue = currentPage.locator(selector).input_value()

	while readValue != value and retries > 0:
		currentPage.fill(selector, value)
		currentPage.wait_for_timeout(50)
		readValue = currentPage.locator(selector).input_value()
		retries -= 1

	return readValue == value

def wp_logon(browser: Browser, logonPageUrl: str, userName: str, password: str, retries: int, verbose: bool = True) -> Page:
	context = browser.new_context(viewport={
		"width": WPTS_CONTEXT.viewPortWidth, 
		"height": WPTS_CONTEXT.viewPortHeight
	})

	# Always use incognito contexts
	logonPage = context.new_page()
	wpts_goto(logonPage, logonPageUrl)

	if (verbose):
		logonPage.on("request", lambda r:
			WPTS_CONSOLE.print(f'>> {r.method} {r.url}', style="bold yellow3")
			if r.is_navigation_request() else None)

		logonPage.on("response", lambda r:
			WPTS_CONSOLE.print(f'<< {r.status} {r.url} -> {r.headers.get("location", "")}', style="bold yellow3")
			if r.request.is_navigation_request() else None)

		logonPage.on("requestfailed", lambda r:
			WPTS_CONSOLE.print(f'FAIL {r.url} {r.failure}', style="bold red")
			if r.is_navigation_request() else None)

	# Check if captcha is still there and bail if its

	# Fill in the logon form and wait for the 
	# WordPress built-in auto-focus timeout to pass
	logonPage.wait_for_timeout(200)

	# We use this helper because the value may not be set at once, 
	# mostly due to the auto-focus we just talked about
	fillOK = wpts_fill_field(logonPage, "#user_login", userName)
	if (not fillOK):
		raise SystemExit("Failed to fill in username at logon!")

	fillOK = wpts_fill_field(logonPage, "#user_pass", password)
	if (not fillOK):
		raise SystemExit("Failed to fill in password at logon!")
	
	logonPage.click("#wp-submit")

	while retries > 0:
		try:
			logonPage.wait_for_url("**/wp-admin**", 
				timeout=WPTS_TIMEOUT, 
				wait_until="domcontentloaded")
			break
		except TimeoutError as timeoutErr:
			WPTS_CONSOLE.print(f':pile_of_poo: Logon timed out while waiting for wp-admin redirect. Current UR: {logonPage.url}.')
			wpLogonError = logonPage.locator("#login_error").all_text_contents()

			if (wpLogonError):
				WPTS_CONSOLE.print(f':pile_of_poo: Got WordPress logon error: {wpLogonError}.', style="bold red")

			retries -= 1
			if (retries == 0):
				raise timeoutErr

	return logonPage

def wp_change_language_to(currentPage: Page, settingPageUrl: str, langCode: str = "") -> str:
	previousUrl = currentPage.url
	wpts_goto(currentPage, settingPageUrl)

	currentPage.wait_for_selector("#WPLANG", 
		state="visible", 
		timeout=WPTS_TIMEOUT)

	wpLang = currentPage.locator("#WPLANG")
	oldLanguageCode = wpLang.input_value()

	if (oldLanguageCode != langCode):
		wpLang.select_option(langCode)

		readLanguageCode = wpLang.input_value()
		if (readLanguageCode != langCode):
			raise SystemExit('Could not set language code')

		currentPage.click("#submit")
		currentPage.wait_for_selector("#setting-error-settings_updated", 
			state="visible", 
			timeout=WPTS_TIMEOUT)

	wpts_goto(currentPage, previousUrl)
	return oldLanguageCode

def wpts_about_screenshot(currentPage: Page, url: str, includeFullPage: bool = False) -> Page:
	wpts_goto(currentPage, url)

	outFile = wpts_screenshot_page(currentPage, outFile="wpts-about.png")
	wpts_register_screenshot(WptsScreenshot(
		page="Admin About",
		url=url,
		outFile=outFile
	))

	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage, 
			outFile="wpts-about-full.png", 
			fullPage=True)
		
		wpts_register_screenshot(WptsScreenshot(
			page="Admin About - Full Page",
			url=url,
			outFile=outFileFull,
			isFullPage=True
		))

	return currentPage

def wpts_navigate_to_settings_tab(currentPage: Page, tab: str, container: str, waitAnimationTimeout: int = 500):
	currentPage.click(tab)
	currentPage.wait_for_selector(container, timeout=WPTS_TIMEOUT)
	#Tab selections highglights ins and outs 
	# animate so we need to wait for them 
	# otherwise we won't screen cap them 
	# in the correct state
	currentPage.wait_for_timeout(waitAnimationTimeout)

def wpts_settings_screenshots(currentPage: Page, url: str, includeFullPage: bool = False) -> Page:
	wpts_goto(currentPage, url)

	#General settings section
	wpts_navigate_to_settings_tab(currentPage, 
		tab="#abp01-general-settings-tab", 
		container="#abp01-general-settings")

	outFile = wpts_screenshot_page(currentPage, outFile="wpts-settings-general.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Admin Settings - General",
		url = url,
		outFile = outFile
	))

	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage, 
			outFile="wpts-settings-general-full.png", 
			fullPage=True)
		
		wpts_register_screenshot(WptsScreenshot(
			page = "Admin Settings - General - Full Page",
			url = url,
			outFile = outFileFull,
			isFullPage = True
		))

	#Viewer settings section
	wpts_navigate_to_settings_tab(currentPage, 
		tab="#abp01-viewer-settings-tab", 
		container="#abp01-viewer-settings")

	outFile = wpts_screenshot_page(currentPage, outFile="wpts-settings-viewer.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Admin Settings - Viewer",
		url = url,
		outFile = outFile
	))
	
	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage, 
			outFile="wpts-settings-viewer-full.png", 
			fullPage=True)

		wpts_register_screenshot(WptsScreenshot(
			page = "Admin Settings - Viewer - Full Page",
			url = url,
			outFile = outFileFull,
			isFullPage = True
		))

	#Map settings section
	wpts_navigate_to_settings_tab(currentPage, 
		tab="#abp01-map-settings-tab", 
		container="#abp01-map-settings")

	#Make sure API key not visible - won't be saved
	currentPage.fill("#abp01-tileLayerApiKey", "")
	
	outFile = wpts_screenshot_page(currentPage, outFile="wpts-settings-map.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Admin Settings - Map",
		url = url,
		outFile = outFile
	))
	
	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage, 
			outFile="wpts-settings-map-full.png", 
			fullPage=True)
		
		wpts_register_screenshot(WptsScreenshot(
			page = "Admin Settings - Map - Full Page",
			url = url,
			outFile = outFileFull,
			isFullPage = True
		))

	#Also screencap the pre-defined tile layers modal
	currentPage.click("#abp01-predefined-tile-layer-selector")
	currentPage.wait_for_selector("#abp01-predefined-tile-layers-window", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(500)

	outFile = wpts_screenshot_page(currentPage, 'wpts-settings-map-predefined-layers.png')
	wpts_register_screenshot(WptsScreenshot(
		page = "Admin Settings - Map - Pre-defined tile layers",
		url = url,
		outFile = outFile
	))

	return currentPage

def wpts_maintenance_screenshots(currentPage: Page, url: str, includeFullPage: bool = False) -> Page:
	wpts_goto(currentPage, url)

	#Just landed on the page, nothing selected
	outFile = wpts_screenshot_page(currentPage, outFile="wpts-maintenance-default.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Admin - Maintenance - Entry",
		outFile = outFile,
		url = url
	))
	
	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage, 
			"wpts-maintenance-default-full.png", 
			fullPage=True)

		wpts_register_screenshot(WptsScreenshot(
				page = "Admin - Maintenance - Entry - Full PAge",
				outFile = outFileFull,
				url = url,
				isFullPage=True
			))

	#Sample 1 - Detect missing track files
	wpts_execute_maintenance_tool(currentPage, 
		option="detect-missing-track-files", 
		waitForSelector="#abp01-admin-missing-tracks-posts")

	outFile = wpts_screenshot_page(currentPage, outFile="wpts-maintenance-missing-track-files.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Admin - Maintenance - Detect Missing Track Files",
		outFile = outFile,
		url = url
	))

	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage, 
			outFile="wpts-maintenance-missing-track-files-full.png", 
			fullPage=True)

		wpts_register_screenshot(WptsScreenshot(
			page = "Admin - Maintenance - Detect Missing Track Files - Full Page",
			outFile = outFileFull,
			url = url,
			isFullPage=True
		))	

	#Sample 2 - Nginx Access Directives Helper
	wpts_execute_maintenance_tool(currentPage, 
		option="nginx-access-directives-helper", 
		waitForSelector="#wpts-nginx-directives-container")

	outFile = wpts_screenshot_page(currentPage, outFile="wpts-maintenance-nginx.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Admin - Maintenance - Nginx Access Directives Helper",
		url=url,
		outFile=outFile
	))

	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage, 
			outFile="wpts-maintenance-nginx-full.png", 
			fullPage=True)

		wpts_register_screenshot(WptsScreenshot(
			page = "Admin - Maintenance - Nginx Access Directives Helper - Full Page",
			url=url,
			outFile=outFileFull,
			isFullPage=True
		))
	
	return currentPage

def wpts_execute_maintenance_tool(currentPage: Page, option: str, waitForSelector: str):
	currentPage.select_option("#abp01-maintenance-tool-select", option)
	currentPage.click("#abp01-execute-maintenance-tool")

	# Every tool execution has a yes/no confirmation
	currentPage.wait_for_selector("#abp01-confirm-dialog-modal", 
		state="visible", 
		timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(500)

	# Always pick yes to proceed
	currentPage.click("#abp01-confirm-dialog-modal button[data-abp01-modal-action='yes']")
	currentPage.wait_for_timeout(150)

	# Progress element needs to disappear before we proceed
	currentPage.wait_for_selector(waitForSelector, 
		state="visible", 
		timeout=WPTS_TIMEOUT)

	currentPage.wait_for_selector("#abp01-progress-container", 
		state="hidden", 
		timeout=WPTS_TIMEOUT)

	# Make sure any lingering animations go away too
	currentPage.wait_for_timeout(150)

def wpts_system_logs_screenshot(currentPage: Page, systemLogsUrl: str, includeFullPage: bool = False) -> Page:
	wpts_goto(currentPage, systemLogsUrl)

	# Wait for logs to load
	currentPage.wait_for_selector("#abp01-progress-container", 
		state="hidden", 
		timeout=WPTS_TIMEOUT)

	# Make sure any lingering animations go away too
	currentPage.wait_for_timeout(150)

	outFile = wpts_screenshot_page(currentPage, outFile="wpts-admin-system-logs.png")
	wpts_register_screenshot(WptsScreenshot(
		url=systemLogsUrl,
		page="Admin - System Logs",
		outFile=outFile
	))

	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage, 
			outFile="wpts-admin-system-logs.png", 
			fullPage=True)

		wpts_register_screenshot(WptsScreenshot(
			url=systemLogsUrl,
			page="Admin - System Logs - Full Page",
			outFile=outFileFull,
			isFullPage=True
		))

	return currentPage

def wpts_lookup_data_screenshot(currentPage: Page, lookupDataUrl: str, includeFullPage: bool = False) -> Page:
	wpts_goto(currentPage, lookupDataUrl)

	# Wait for the initial lookup request to start and finish, even for an empty list.
	currentPage.wait_for_selector("#abp01-progress-container",
		state="attached",
		timeout=WPTS_TIMEOUT)
	currentPage.wait_for_selector("#abp01-progress-container",
		state="hidden",
		timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(150)

	outFile = wpts_screenshot_page(currentPage, outFile="wpts-admin-lookup-data.png")
	wpts_register_screenshot(WptsScreenshot(
		url=lookupDataUrl,
		page="Admin - Lookup Data",
		outFile=outFile
	))

	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage,
			outFile="wpts-admin-lookup-data-full.png",
			fullPage=True)

		wpts_register_screenshot(WptsScreenshot(
			url=lookupDataUrl,
			page="Admin - Lookup Data - Full Page",
			outFile=outFileFull,
			isFullPage=True
		))

	# Open the add form without saving a lookup item.
	currentPage.click("#abp01-add-lookup-top")
	currentPage.wait_for_selector("#abp01-edit-lookup-window",
		state="visible",
		timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(500)

	outFile = wpts_screenshot_page(currentPage, outFile="wpts-admin-lookup-data-add.png")
	wpts_register_screenshot(WptsScreenshot(
		url=lookupDataUrl,
		page="Admin - Lookup Data - Add Item",
		outFile=outFile
	))

	return currentPage

def wpts_post_listing_screenshot(currentPage: Page, url: str, postId: str, includeFullPage: bool = False) -> Page:
	
	return currentPage

def wpts_post_edit_screenshot(currentPage: Page, url: str, postId: str, includeFullPage: bool = False) -> Page:

	return currentPage

def wpts_post_view_screenshot(currentPage: Page, postViewUrl: str, includeFullPage: bool = False) -> Page:
	wpts_goto(currentPage, postViewUrl)

	# Top teaser - capture element-only
	currentPage.wait_for_selector("#abp01-techbox-teaser", timeout=WPTS_TIMEOUT)
	teaser = currentPage.locator("#abp01-techbox-teaser")
	outFile = wpts_screenshot_locator(teaser, outFile="wpts-sample-post-teaser.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Frontend - View Post",
		element = "Top Teaser",
		outFile = outFile,
		url = postViewUrl
	))

	# Move down and wait for the viewer to come into view
	currentPage.click("#abp01-techbox-teaser-action")
	currentPage.wait_for_timeout(150)

	viewer = currentPage.locator("#abp01-techbox-frontend")
	expect(viewer).to_be_in_viewport()

	# Info tab, grab that
	currentPage.click("#abp01-tab-info a")
	currentPage.wait_for_selector("#abp01-techbox-info", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(150)

	outFile = wpts_screenshot_locator(viewer, outFile="wpts-sample-post-viewer-info.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Frontend - View Post",
		element = "Viewer - Info Tab",
		outFile = outFile,
		url = postViewUrl
	))

	# Map tab with and without altitudine profile
	currentPage.click("#abp01-tab-map a")
	currentPage.wait_for_selector("#abp01-techbox-map", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_selector("#abp01-map .leaflet-tile", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(150)

	outFile = wpts_screenshot_locator(viewer, outFile="wpts-sample-post-viewer-map.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Frontend - View Post",
		element = "Viewer - Map Tab",
		outFile = outFile,
		url = postViewUrl
	))

	map = currentPage.locator("#abp01-map")
	profileButton = map.locator(".abp01-map-altitude-profile-btn >> ..")
	profileButton.click()

	currentPage.wait_for_selector("#abp01-altitude-profile-container", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(150)

	outFile = wpts_screenshot_locator(viewer, outFile="wpts-sample-post-viewer-map-alt-profile.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Frontend - View Post",
		element = "Viewer - Map Tab with Altitude Profile",
		outFile = outFile,
		url = postViewUrl
	))

	# Route log tab
	currentPage.click("#abp01-route-log a")
	currentPage.wait_for_selector("#abp01-route-log-content", state="visible", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(150)

	outFile = wpts_screenshot_locator(viewer, outFile="wpts-sample-post-viewer-log.png")
	wpts_register_screenshot(WptsScreenshot(
		page = "Frontend - View Post",
		element = "Viewer - Route Log Tab",
		outFile = outFile,
		url = postViewUrl
	))

	# Full page, only if requested
	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage, 
			outFile="screenshots/wpts-sample-post-full.png", 
			fullPage=True)

		wpts_register_screenshot(WptsScreenshot(
			page = "Frontend - View Post - Full Page",
			outFile = outFileFull,
			url = postViewUrl,
			isFullPage=True
		))

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


def wpts_setup(force: bool, args: WptsArgs|None = None):
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

def wpts_save_screenshot_registry(screenshots: list[WptsScreenshot], outDir: str) -> str:
	outRecords = []
	outFile = f'{outDir.rstrip('/')}/catalog.json'	

	# WptsScreenshot is not JSON serializable!
	for screenshot in screenshots:
		outRecords.append(asdict(screenshot))

	with open(outFile, 'w', encoding="utf-8") as file:
		json.dump(outRecords, file, indent=4, sort_keys=True)

	return outFile

def wpts_report_screenshot_registry(registry: list[WptsScreenshot]):
	pass

def wpts_begin_status(label: str) -> Status:
	return WPTS_CONSOLE.status(f'[bold green] {label}')
	
def wpts_report_status_task(taskStatus: str, good: bool = True):
	icon = ":thumbs_up:" if good else ":pile_of_poo:"
	WPTS_CONSOLE.log(f'{icon} {taskStatus}', style="bold green")

def main():
	try:
		wpts_setup(force=False)
		config = wpts_config()
		WPTS_CONTEXT.config = config
	except (OSError, ValueError, EOFError) as error:
		raise SystemExit(f"Cannot configure screen capture: {error}") from None
	except KeyboardInterrupt:
		raise SystemExit("Screen capture setup cancelled.") from None

	with sync_playwright() as playwright:
		browser = playwright.firefox.launch(headless=True)

		try:
			logonUrl = wp_login_url(config.baseUrl)		
			try:
				WPTS_CONSOLE.print(f'Logging on to {config.baseUrl}...', style="bold yellow3")
				wpAdmin = wp_logon(browser, logonUrl,
					userName=config.userName,
					password=config.password,
					retries=3,
					verbose=False)
	
				WPTS_CONSOLE.print(':thumbs_up: Successfully logged on', style="bold green")
			except TimeoutError:
				WPTS_CONSOLE.print(":pile_of_poo: Logon timed out!", style="bold red")
				raise SystemExit(1)
	
			with wpts_begin_status("Capturing screenshots...") as status:
				oldLanguageCode = wp_change_language_to(wpAdmin, 
					wp_options_general_url(config.baseUrl),
					WPTS_CONTEXT.langCode)

				wpts_report_status_task(f'Successfully set langauge code to: {WPTS_CONTEXT.langCode if WPTS_CONTEXT.langCode else "en"}. Old langauge code: {oldLanguageCode if oldLanguageCode else "en"}.')

				wptsAbout = wpts_about_screenshot(wpAdmin, 
					wpts_about_url(config.baseUrl))
				wpts_report_status_task("Captured Admin About page!")
	
				wptsSettings = wpts_settings_screenshots(wptsAbout, 
					wpts_settings_url(config.baseUrl))
				wpts_report_status_task("Captured Admin Settings page!")
	
				wptsMaintenance = wpts_maintenance_screenshots(wptsSettings, 
					wpts_maintenance_url(config.baseUrl))
				wpts_report_status_task("Captured Admin Maintenance page!")

				wptsSystemLogs = wpts_system_logs_screenshot(wptsMaintenance, 
					wpts_admin_system_logs_url(config.baseUrl))
				wpts_report_status_task("Captured Admin System Logs page!")
	
				wptsLookupData = wpts_lookup_data_screenshot(wptsSystemLogs,
					wpts_lookup_data_url(config.baseUrl))
				wpts_report_status_task("Captured Admin Lookup Data page and add form!")

				wptsPostSample = wpts_post_view_screenshot(wptsLookupData,
					wpts_post_url(config.baseUrl, config.knownSamplePostView))
				wpts_report_status_task("Captured Frontend Sample page!")

				wp_change_language_to(wptsPostSample, 
					wp_options_general_url(config.baseUrl), 
					oldLanguageCode)

				wpts_report_status_task(f'Successfully restored langauge code to: {oldLanguageCode if oldLanguageCode else "en"}.')

				filteredOutDir = f'{WPTS_CONTEXT.outDir}/filtered'
				wpts_apply_image_filers(wpts_get_registered_screenshots(), 
					filteredOutDir)
				wpts_report_status_task("Applied filters!")

				wptsPostSample.close()

				catalogFile = wpts_save_screenshot_registry(wpts_get_registered_screenshots(), 
					WPTS_CONTEXT.outDir)
				wpts_report_status_task(f"Saved catalog: {catalogFile}!")
		finally:
			browser.close()

if __name__ == "__main__":
	main()
