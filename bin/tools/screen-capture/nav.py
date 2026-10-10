from playwright.sync_api import Page, Locator
from context import wpts_context_get, wpts_context_outdir

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

def wpts_goto(currentPage: Page, url: str):
	currentPage.goto(url=url, 
		timeout=wpts_context_get().timeout, 
		wait_until="domcontentloaded")

def wpts_screenshot_page(currentPage: Page, outFile: str, fullPage: bool|None = None) -> str:
	outPath = f'{wpts_context_outdir()}/{outFile}'
	currentPage.screenshot(path=outPath, full_page=fullPage)
	return outPath

def wpts_screenshot_locator(locator: Locator, outFile: str):
	outPath = f'{wpts_context_outdir()}/{outFile}'
	locator.screenshot(path=outPath)
	return outPath