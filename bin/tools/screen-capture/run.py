from dataclasses import asdict
from pathlib import Path
from rich.console import Console
from rich.status import Status

from playwright.sync_api import sync_playwright, expect
from playwright.sync_api import Browser
from playwright.sync_api import Page, Locator
from playwright.sync_api import TimeoutError

import cv2
import os
import json
import argparse

from data import WptsArgs, WptsScreenshot
from context import wpts_context_get
from config import wpts_get_config, wpts_setup, wpts_config_value
from image import wpts_apply_soften_filter, wpts_apply_vignette_filter
from registry import wpts_register_screenshot, wpts_get_registered_screenshots
from console import wpts_print_fail, wpts_print_neutral, wpts_print_ok, wpts_begin_status, wpts_report_status_task
from nav import wpts_goto, wpts_screenshot_locator, wpts_screenshot_page
from wp import wp_logon, wp_change_language_to

WPTS_TIMEOUT = 10000

WPTS_ARG_HOST = "--host"
WPTS_ARG_USERNAME = "--user-name"
WPTS_ARG_PASSWORD = "--password"
WPTS_ARG_OUT_DIR = "--out-dir"
WPTS_ARG_VIEWPORT = "--viewport"
WPTS_ARG_SAVE_CONFIG = "--save-config"
WPTS_ARG_RECONFIGURE = "--reconfigure"
WPTS_ARG_FULL_PAGES = "--full-pages"
WPTS_ARG_VERBOSE = "--verbose"

WP_OPTIONS_GENERAL_URL = "options-general.php"
WP_POST_LISTING_URL = "edit.php"
WP_POST_EDIT_URL = "post.php"

WPTS_ABOUT_URL = "admin.php?page=abp01-trip-summary-about"
WPTS_SETTINGS_URL = "admin.php?page=abp01-trip-summary-settings"
WPTS_MAINTENANCE_URL = "admin.php?page=abp01-trip-summary-maintenance"
WPTS_SYSTEM_LOGS_URL = "admin.php?page=abp01-system-logs"
WPTS_LOOKUP_DATA_URL = "admin.php?page=abp01-trip-summary-lookup"

def wp_admin_url(baseUrl:str):
	return baseUrl.rstrip('/') + "/wp-admin/"

def wp_login_url(baseUrl: str):
	return baseUrl.rstrip('/') + "/wp-login.php"

def wp_options_general_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WP_OPTIONS_GENERAL_URL

def wp_post_listing_url(baseUrl: str):
	return wp_admin_url(baseUrl) + WP_POST_LISTING_URL

def wp_post_edit_url(baseUrl: str, postId: str):
	return wp_admin_url(baseUrl) + f"{WP_POST_EDIT_URL}?post={postId}&action=edit"

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
			wpts_print_fail(f'Could not open mage {screen.outFile}')

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

def _wpts_navigate_to_settings_tab(currentPage: Page, tab: str, container: str, waitAnimationTimeout: int = 500):
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
	_wpts_navigate_to_settings_tab(currentPage, 
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
	_wpts_navigate_to_settings_tab(currentPage, 
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
	_wpts_navigate_to_settings_tab(currentPage, 
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
	_wpts_execute_maintenance_tool(currentPage, 
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
	_wpts_execute_maintenance_tool(currentPage, 
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

def _wpts_execute_maintenance_tool(currentPage: Page, option: str, waitForSelector: str):
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
			outFile="wpts-admin-system-logs-full.png",
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
	wpts_goto(currentPage, url)
	currentPage.wait_for_load_state("load", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_selector("#the-list", state="visible", timeout=WPTS_TIMEOUT)

	outFile = wpts_screenshot_page(currentPage, outFile="wpts-admin-post-listing.png")
	wpts_register_screenshot(WptsScreenshot(
		url=currentPage.url,
		page="Admin - Post Listing",
		outFile=outFile
	))

	if (includeFullPage):
		outFileFull = wpts_screenshot_page(currentPage,
			outFile="wpts-admin-post-listing-full.png",
			fullPage=True)

		wpts_register_screenshot(WptsScreenshot(
			url=currentPage.url,
			page="Admin - Post Listing - Full Page",
			outFile=outFileFull,
			isFullPage=True
		))

	# The configured sample post may be on a later listing page.
	postRow = _wpts_listing_search_for_target_post(currentPage, postId)

	# Wait for the audit controls to initialize before revealing the row actions.
	currentPage.wait_for_load_state("load", timeout=WPTS_TIMEOUT)
	currentPage.wait_for_selector("#abp01-listing-audit-log-window-__wrap",
		state="attached",
		timeout=WPTS_TIMEOUT)
	postRow.hover(timeout=WPTS_TIMEOUT)
	postRow.locator(f"a.abp01-admin-listing-audit-log-link[data-post='{postId}']")\
		.click(timeout=WPTS_TIMEOUT)

	# The modal is populated only after the audit request completes.
	currentPage.wait_for_selector("#abp01-listing-audit-log-window .abp01-admin-trip-summary-audit-log",
		state="visible",
		timeout=WPTS_TIMEOUT)
	currentPage.wait_for_selector("#abp01-progress-container",
		state="hidden",
		timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(500)

	outFile = wpts_screenshot_page(currentPage, outFile="wpts-admin-post-listing-audit.png")
	wpts_register_screenshot(WptsScreenshot(
		url=currentPage.url,
		page=f"Admin - Post Listing - Audit Log Sample",
		outFile=outFile
	))

	auditLogWindow = currentPage.locator("#abp01-listing-audit-log-window-content")
	outFile = wpts_screenshot_locator(auditLogWindow, 
		outFile="wpts-admin-post-listing-audit-window.png")

	wpts_register_screenshot(WptsScreenshot(
		url=currentPage.url,
		page=f"Admin - Post Listing - Audit Log Sample",
		outFile=outFile,
		element="Audit Log Window"
	))

	return currentPage

def _wpts_listing_search_for_target_post(currentPage: Page, postId: str) -> Locator:
	postRow = currentPage.locator(f"#post-{postId}")
	while postRow.count() == 0:
		nextPage = currentPage.locator("#posts-filter .tablenav.top a.next-page:not(.disabled)")
		if nextPage.count() == 0:
			raise ValueError(f"Post {postId} was not found in the post listing [1].")

		nextPageUrl = nextPage.get_attribute("href")
		if (nextPageUrl is not None):
			wpts_goto(currentPage, nextPageUrl)
			currentPage.wait_for_selector("#the-list", state="visible", timeout=WPTS_TIMEOUT)
		else:
			raise ValueError(f"Post {postId} was not found in the post listing [2].")

	return postRow

def wpts_post_edit_screenshot(currentPage: Page, url: str, postId: str, includeFullPage: bool = False) -> Page:
	wpts_goto(currentPage, url)
	currentPage.wait_for_load_state("load", timeout=WPTS_TIMEOUT)
	expect(currentPage.locator("#post_ID")).to_have_value(str(postId), timeout=WPTS_TIMEOUT)

	launcher = currentPage.locator("#abp01-enhanced-editor-launcher-metabox")
	routeLog = currentPage.locator("#abp01-enhanced-editor-log-metabox")
	for metabox in (launcher, routeLog):
		metabox.wait_for(state="visible", timeout=WPTS_TIMEOUT)
		
		# Metaboxes may be collapsed
		if "closed" in (metabox.get_attribute("class") or "").split():
			metabox.locator("button.handlediv").click(timeout=WPTS_TIMEOUT)

		metabox.locator(".inside").wait_for(state="visible", timeout=WPTS_TIMEOUT)

	outFile = wpts_screenshot_locator(launcher, outFile="wpts-admin-post-edit-launcher.png")
	wpts_register_screenshot(WptsScreenshot(
		url=url,
		page="Admin - Edit Post",
		element="Trip Summary Launcher Metabox",
		outFile=outFile
	))

	# Capture the editor itself on each tab, without saving any changes.
	launcher.locator("a[data-action='abp01-openTechBox'][data-select-tab='abp01-form-info']")\
		.click(timeout=WPTS_TIMEOUT)
	
	editor = currentPage.locator("#abp01-techbox-editor")
	editor.wait_for(state="visible", timeout=WPTS_TIMEOUT)

	currentPage.wait_for_selector("#abp01-form-info > div", 
		state="visible", 
		timeout=WPTS_TIMEOUT)
	currentPage.wait_for_timeout(500)

	outFile = wpts_screenshot_locator(editor, 
		outFile="wpts-admin-post-edit-info.png")
	
	wpts_register_screenshot(WptsScreenshot(
		url=url,
		page="Admin - Edit Post",
		element="Trip Summary Editor - Info",
		outFile=outFile
	))

	editor.locator("#abp01-tab-map a")\
		.click(timeout=WPTS_TIMEOUT)
	
	currentPage.wait_for_selector("#abp01-form-map > div", 
		state="visible", 
		timeout=WPTS_TIMEOUT)

	if editor.locator("#abp01-map").count() > 0:
		# Track data arrives via AJAX; map tiles finish loading separately.
		currentPage.wait_for_function("""() => {
			const tiles = Array.from(document.querySelectorAll('#abp01-map .leaflet-tile'));
			return tiles.length > 0 && tiles.every(tile => tile.complete && tile.naturalWidth > 0);
		}""", timeout=WPTS_TIMEOUT)

		editor.locator(".abp01-progress-container")\
			.wait_for(state="hidden", timeout=WPTS_TIMEOUT)
		
	currentPage.wait_for_timeout(500)

	outFile = wpts_screenshot_locator(editor, outFile="wpts-admin-post-edit-map.png")
	wpts_register_screenshot(WptsScreenshot(
		url=url,
		page="Admin - Edit Post",
		element="Trip Summary Editor - Map",
		outFile=outFile
	))

	editor.locator("a[data-action='abp01-closeTechBox']")\
		.click(timeout=WPTS_TIMEOUT)
	editor.wait_for(state="hidden", timeout=WPTS_TIMEOUT)

	outFile = wpts_screenshot_locator(routeLog, outFile="wpts-admin-post-edit-route-log.png")
	wpts_register_screenshot(WptsScreenshot(
		url=url,
		page="Admin - Edit Post",
		element="Route Log Metabox",
		outFile=outFile
	))

	# The add form is available even when the post has no route log entries.
	routeLog.locator("#abp01-addTripSummary-logEntry")\
		.click(timeout=WPTS_TIMEOUT)
	
	routeLogForm = currentPage.locator("#abp01-tripSummaryLog-formContainer")
	routeLogForm.wait_for(state="visible", timeout=WPTS_TIMEOUT)

	currentPage.wait_for_timeout(500)

	outFile = wpts_screenshot_locator(routeLogForm, outFile="wpts-admin-post-edit-route-log-form.png")
	wpts_register_screenshot(WptsScreenshot(
		url=url,
		page="Admin - Edit Post",
		element="Route Log - Add Entry Form",
		outFile=outFile
	))

	routeLogForm.locator("#abp01-cancel-logEntry").click(timeout=WPTS_TIMEOUT)
	routeLogForm.wait_for(state="hidden", timeout=WPTS_TIMEOUT)

	if (includeFullPage):
		# Reset sticky WordPress controls after the element captures scrolled the page.
		currentPage.evaluate("window.scrollTo(0, 0)")
		currentPage.wait_for_timeout(500)

		outFileFull = wpts_screenshot_page(currentPage,
			outFile="wpts-admin-post-edit-full.png",
			fullPage=True)
		
		wpts_register_screenshot(WptsScreenshot(
			url=url,
			page="Admin - Edit Post - Full Page",
			outFile=outFileFull,
			isFullPage=True
		))

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
			outFile="wpts-sample-post-full.png",
			fullPage=True)

		wpts_register_screenshot(WptsScreenshot(
			page = "Frontend - View Post - Full Page",
			outFile = outFileFull,
			url = postViewUrl,
			isFullPage=True
		))

	return currentPage

def wpts_parse_args() -> WptsArgs:
	wptsArgs = WptsArgs()
	parser = argparse.ArgumentParser()

	parser.add_argument(WPTS_ARG_HOST, required=False, 
		help="Use this host. Must include protocol, i.e. http:// or https://. Takes precedence over the configured one.")
	parser.add_argument(WPTS_ARG_USERNAME, dest="userName", required=False, 
		help="Use this username for WordPress logon. Takes precedence over the configured one.")
	parser.add_argument(WPTS_ARG_PASSWORD, dest="password", required=False, 
		help="Use this password for WordPress logon. Takes precedence over the configured one.")
	parser.add_argument(WPTS_ARG_OUT_DIR, dest="outDir", required=False, 
		default="./screenshots",
		help="Where to save screenshots")
	parser.add_argument(WPTS_ARG_VIEWPORT, dest="viewport", required=False, 
		default="1920x1080", 
		help="Resolution to use, WIDTHxHEIGHT format")
	parser.add_argument(WPTS_ARG_SAVE_CONFIG, dest="saveCofig", action="store_true", 
		required=False, 
		default=False,
		help="Save host, username and password in current configuration")
	parser.add_argument(WPTS_ARG_RECONFIGURE, dest="reconfigure", action="store_true", 
		required=False, 
		default=False,
		help="Restart setup process, will use any current values that overlap as defaults")
	parser.add_argument(WPTS_ARG_FULL_PAGES, dest="fullPages", action="store_true", 
		required=False, 
		default=False,
		help="Whether to include full pages or not")
	
	parser.add_argument(WPTS_ARG_VERBOSE, dest="verbose", action="store_true", 
		required=False, 
		default=False,
		help="Enable advanced tracing")

	parser.parse_args(namespace=wptsArgs)
	return wptsArgs

def wpts_validate_args_or_throw(wptsArgs: WptsArgs) -> None:
	for option, configField, value in (
		(WPTS_ARG_HOST, "baseUrl", wptsArgs.host),
		(WPTS_ARG_USERNAME, "userName", wptsArgs.userName),
		(WPTS_ARG_PASSWORD, "password", wptsArgs.password)
	):
		if value is not None:
			try:
				wpts_config_value(configField, value)
			except ValueError as error:
				raise ValueError(f"{option}: {error}") from None

	if not isinstance(wptsArgs.outDir, str) or not wptsArgs.outDir.strip() or "\0" in wptsArgs.outDir:
		raise ValueError(f"{WPTS_ARG_OUT_DIR} must be a non-empty directory path without null characters.")

	# A new output directory is valid, provided no existing parent is a file.
	outDir = Path(wptsArgs.outDir)
	for candidate in (outDir, *outDir.parents):
		if candidate.exists() or candidate.is_symlink():
			if not candidate.is_dir():
				raise ValueError(f"{WPTS_ARG_OUT_DIR} must refer to a directory, with no file in its parent path.")
			break

	_wpts_parse_viewport_spec(wptsArgs.viewport)

	for option, value in (
		(WPTS_ARG_SAVE_CONFIG, wptsArgs.saveCofig),
		(WPTS_ARG_RECONFIGURE, wptsArgs.reconfigure),
		(WPTS_ARG_FULL_PAGES, wptsArgs.fullPages),
		(WPTS_ARG_VERBOSE, wptsArgs.verbose)
	):
		if type(value) is not bool:
			raise ValueError(f"{option} must be a boolean flag.")

def wpts_save_screenshot_registry(screenshots: list[WptsScreenshot], outDir: str) -> str:
	outRecords = []
	outFile = f'{outDir.rstrip('/')}/catalog.json'	

	# WptsScreenshot is not JSON serializable!
	for screenshot in screenshots:
		outRecords.append(asdict(screenshot))

	with open(outFile, 'w', encoding="utf-8") as file:
		json.dump(outRecords, file, indent=4, sort_keys=True)

	return outFile

def wpts_report_screenshot_registry(screenshots: list[WptsScreenshot]):
	pass

def _wpts_parse_viewport_spec(viewportSpec: str) -> tuple[int, int]:
	message = f"{WPTS_ARG_VIEWPORT} must use WIDTHxHEIGHT with two positive integers, e.g. 1920x1080."
	if not isinstance(viewportSpec, str):
		raise ValueError(message)

	dimensions = [value.strip() for value in viewportSpec.lower().split("x")]
	if len(dimensions) != 2 or any(not value.isascii() or not value.isdecimal() for value in dimensions):
		raise ValueError(message)

	try:
		width, height = (int(value) for value in dimensions)
	except ValueError:
		raise ValueError(message) from None
	if width <= 0 or height <= 0:
		raise ValueError(message)

	return width, height

def wpts_parse_viewport_spec_into_context(wptsArgs: WptsArgs) -> None:
	width, height = _wpts_parse_viewport_spec(wptsArgs.viewport)
	wptsContext = wpts_context_get()
	wptsContext.viewPortWidth = width
	wptsContext.viewPortHeight = height

def main():
	try:
		wptsArgs = wpts_parse_args()
		wptsContext = wpts_context_get()
		wpts_validate_args_or_throw(wptsArgs)

		wpts_setup(force=wptsArgs.reconfigure)		
		config = wpts_get_config()
		
		wptsContext.config = config
		wptsContext.outDir = wptsArgs.outDir

		wpts_parse_viewport_spec_into_context(wptsArgs)
	except (OSError, ValueError, EOFError) as error:
		raise SystemExit(f"Cannot configure screen capture: {error}") from None
	except KeyboardInterrupt:
		raise SystemExit("Screen capture setup cancelled.") from None

	with sync_playwright() as playwright:
		browser = playwright.firefox.launch(headless=True)

		try:
			logonUrl = wp_login_url(config.baseUrl)		
			try:
				wpts_print_neutral(f'Logging on to {config.baseUrl}...')
				wpAdmin = wp_logon(browser, logonUrl,
					userName=config.userName,
					password=config.password,
					retries=3,
					verbose=wptsArgs.verbose)
	
				wpts_print_ok('Successfully logged on')
			except TimeoutError:
				wpts_print_fail("Logon timed out!")
				raise SystemExit(1)
	
			with wpts_begin_status("Capturing screenshots...") as status:
				oldLanguageCode = wp_change_language_to(wpAdmin, 
					wp_options_general_url(config.baseUrl),
					wptsContext.langCode)

				wpts_report_status_task(f'Successfully set langauge code to: {wptsContext.langCode if wptsContext.langCode else "en"}. Old langauge code: {oldLanguageCode if oldLanguageCode else "en"}.')

				wptsAbout = wpts_about_screenshot(wpAdmin, 
					wpts_about_url(config.baseUrl),
					includeFullPage=wptsArgs.fullPages)
				wpts_report_status_task("Captured Admin About page!")
	
				wptsSettings = wpts_settings_screenshots(wptsAbout, 
					wpts_settings_url(config.baseUrl),
					includeFullPage=wptsArgs.fullPages)
				wpts_report_status_task("Captured Admin Settings page!")
	
				wptsMaintenance = wpts_maintenance_screenshots(wptsSettings, 
					wpts_maintenance_url(config.baseUrl),
					includeFullPage=wptsArgs.fullPages)
				wpts_report_status_task("Captured Admin Maintenance page!")

				wptsSystemLogs = wpts_system_logs_screenshot(wptsMaintenance, 
					wpts_admin_system_logs_url(config.baseUrl),
					includeFullPage=wptsArgs.fullPages)
				wpts_report_status_task("Captured Admin System Logs page!")
	
				wptsLookupData = wpts_lookup_data_screenshot(wptsSystemLogs,
					wpts_lookup_data_url(config.baseUrl),
					includeFullPage=wptsArgs.fullPages)
				wpts_report_status_task("Captured Admin Lookup Data page and add form!")

				wptsPostListing = wpts_post_listing_screenshot(wptsLookupData,
					wp_post_listing_url(config.baseUrl),
					postId=config.knownSamplePostEdit,
					includeFullPage=wptsArgs.fullPages)
				wpts_report_status_task("Captured Admin Post Listing page and audit log!")

				wptsPostEdit = wpts_post_edit_screenshot(wptsPostListing,
					wp_post_edit_url(config.baseUrl, config.knownSamplePostEdit),
					postId=config.knownSamplePostEdit,
					includeFullPage=wptsArgs.fullPages)
				wpts_report_status_task("Captured Admin Post Editor elements!")

				wptsPostSample = wpts_post_view_screenshot(wptsPostEdit,
					wpts_post_url(config.baseUrl, config.knownSamplePostView),
					includeFullPage=wptsArgs.fullPages)
				wpts_report_status_task("Captured Frontend Sample page!")

				wp_change_language_to(wptsPostSample, 
					wp_options_general_url(config.baseUrl), 
					oldLanguageCode)

				wpts_report_status_task(f'Successfully restored langauge code to: {oldLanguageCode if oldLanguageCode else "en"}.')

				filteredOutDir = f'{wptsContext.outDir}/filtered'
				wpts_apply_image_filers(wpts_get_registered_screenshots(), 
					filteredOutDir)
				wpts_report_status_task("Applied filters!")

				wptsPostSample.close()

				catalogFile = wpts_save_screenshot_registry(wpts_get_registered_screenshots(), 
					wptsContext.outDir)
				wpts_report_status_task(f"Saved catalog: {catalogFile}!")
		finally:
			browser.close()

if __name__ == "__main__":
	main()
