from playwright.sync_api import Browser, Page
from context import wpts_context_get
from console import wpts_print_ok, wpts_print_fail, wpts_print_neutral
from nav import wpts_goto, wpts_fill_field

def wp_logon(browser: Browser, logonPageUrl: str, userName: str, password: str, retries: int, verbose: bool = True) -> Page:
	wptsContext = wpts_context_get()
	context = browser.new_context(viewport={
		"width": wptsContext.viewPortWidth, 
		"height": wptsContext.viewPortHeight
	})

	# Always use incognito contexts
	logonPage = context.new_page()
	wpts_goto(logonPage, logonPageUrl)

	if (verbose):
		logonPage.on("request", lambda r:
			wpts_print_neutral(f'>> {r.method} {r.url}')
			if r.is_navigation_request() else None)

		logonPage.on("response", lambda r:
			wpts_print_neutral(f'<< {r.status} {r.url} -> {r.headers.get("location", "")}')
			if r.request.is_navigation_request() else None)

		logonPage.on("requestfailed", lambda r:
			wpts_print_fail(f'FAIL {r.url} {r.failure}')
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
				timeout=wptsContext.timeout, 
				wait_until="domcontentloaded")
			break
		except TimeoutError as timeoutErr:
			wpts_print_fail(f'Logon timed out while waiting for wp-admin redirect. Current UR: {logonPage.url}.')
			wpLogonError = logonPage.locator("#login_error").all_text_contents()

			if (wpLogonError):
				wpts_print_fail(f'Got WordPress logon error: {wpLogonError}.')

			retries -= 1
			if (retries == 0):
				raise timeoutErr

	return logonPage

def wp_change_language_to(currentPage: Page, settingPageUrl: str, langCode: str = "") -> str:
	wptsContext = wpts_context_get()
	previousUrl = currentPage.url
	wpts_goto(currentPage, settingPageUrl)

	currentPage.wait_for_selector("#WPLANG", 
		state="visible", 
		timeout=wptsContext.timeout)

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
			timeout=wptsContext.timeout)

	wpts_goto(currentPage, previousUrl)
	return oldLanguageCode