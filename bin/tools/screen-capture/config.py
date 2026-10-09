import yaml
from getpass import getpass
from pathlib import Path
from data import WptsArgs, WptsConfig
from tempfile import NamedTemporaryFile
from urllib.parse import urlsplit

WPTS_CONFIG_PATH = Path("./config.yaml")
WPTS_CONFIG_FIELDS = {
	"baseUrl": "WordPress base URL",
	"userName": "WordPress username",
	"password": "WordPress password",
	"knownSamplePostEdit": "Sample post ID for editor screenshots",
	"knownSamplePostView": "Sample post permalink for viewer screenshots"
}

def wpts_setup(force: bool, wptsArgs: WptsArgs|None = None):
	"""Reuse a valid config, or interactively collect and save all required values."""
	try:
		currentConfig = wpts_get_config()
	except (FileNotFoundError, ValueError):
		currentConfig = None

	if currentConfig is not None and not force:
		return

	# If command line args have some values, merged those in
	if (wptsArgs is not None and currentConfig is not None):
		currentConfig.baseUrl = wptsArgs.host if wptsArgs.host \
			else currentConfig.baseUrl
		currentConfig.userName = wptsArgs.userName if wptsArgs.userName \
			else currentConfig.userName
		currentConfig.password = wptsArgs.password if wptsArgs.password \
			else currentConfig.password

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
				configData[name] = wpts_config_value(name, value)
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


def wpts_get_config() -> WptsConfig:
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
		name: wpts_config_value(name, configData.get(name))
		for name in WPTS_CONFIG_FIELDS
	})

def wpts_config_value(name: str, value) -> str:
	"""Cleans and validates config value"""
	
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