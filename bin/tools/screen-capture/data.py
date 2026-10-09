from dataclasses import dataclass, field, asdict

@dataclass
class WptsArgs:
	# Use this host, --host
	host: str| None = None
	# Use this username for logon, --username
	userName: str | None = None
	# Use this password for logon, --password
	password: str | None = None
	# Where to save screenshots, --output-dir
	outDir: str = "./screenshots"
	# Resolution to use, WIDTHxHEIGHT format, --viewport
	viewport: str = "1920x1080"
	# Override host, username and password in current configuration, --save-config
	saveCofig: bool = False
	# Restart setup process, will use any given values that overlap as defaults, --reconfigure
	reconfigure: bool = False
	# Whether to include full pages or not, --full-pages
	fullPages: bool = False
	# Whether to enable advanced tracing
	verbose: bool = False

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