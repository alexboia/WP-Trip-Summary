from data import WptsScreenshot, WptsContext
from context import wpts_context_config

WPTS_SCREENSHOT_REGISTRY: list[WptsScreenshot] = []

def wpts_to_relative_url(url: str) -> str:
	config = wpts_context_config()
	return url.replace(config.baseUrl, "").lstrip('/') \
		if config is not None \
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