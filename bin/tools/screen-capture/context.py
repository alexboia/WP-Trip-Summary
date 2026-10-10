from data import WptsContext, WptsConfig

WPTS_CONTEXT = WptsContext()

def wpts_context_get() -> WptsContext:
	return WPTS_CONTEXT

def wpts_context_update(context: WptsContext) -> WptsContext:
	WPTS_CONTEXT.config = context.config
	WPTS_CONTEXT.outDir = context.outDir
	WPTS_CONTEXT.langCode = context.langCode
	WPTS_CONTEXT.viewPortHeight = context.viewPortHeight
	WPTS_CONTEXT.viewPortWidth = context.viewPortWidth
	return WPTS_CONTEXT

def wpts_context_outdir() -> str:
	return WPTS_CONTEXT.outDir

def wpts_context_config() -> WptsConfig|None:
	return WPTS_CONTEXT.config