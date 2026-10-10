import os
from rich.console import Console
from rich.status import Status
from rich.table import Table
from data import WptsScreenshot

WPTS_CONSOLE = Console()

def wpts_print_table(table: Table, sep: int = 1):
	before = after = "\n" * sep if sep > 0 else ""
	
	print(before) if before else None
	WPTS_CONSOLE.print(table)
	print(after) if after else None

def wpts_print_neutral(message: str):
	WPTS_CONSOLE.print(message, style="bold yellow3")

def wpts_print_fail(message: str):
	WPTS_CONSOLE.print(f':pile_of_poo: {message}', style="bold red")

def wpts_print_ok(message: str):
	WPTS_CONSOLE.print(f':thumbs_up: {message}', style="bold green")

def wpts_begin_status(label: str) -> Status:
	return WPTS_CONSOLE.status(f'[bold green] {label}')

def wpts_report_status_task(taskStatus: str, good: bool = True):
	icon = ":thumbs_up:" if good else ":pile_of_poo:"
	WPTS_CONSOLE.log(f'{icon} {taskStatus}', style="bold green")

def wpts_displayable_table_from_screenshot_registry(screenshots: list[WptsScreenshot]) -> Table:	
	table = Table(show_header=True, header_style="bold magenta")
	table.add_column("Page", style="bold blue")
	table.add_column("URL", overflow="fold", style="dim")
	table.add_column("File", overflow="fold", style="bold yellow")
	table.add_column("Full Page", style="dim")
	table.add_column("Element", style="bold blue")

	for screenshot in screenshots:
		table.add_row(screenshot.page, 
			screenshot.url,
			os.path.basename(screenshot.outFile), 
			"Yes" if screenshot.isFullPage else "No", 
			screenshot.element if screenshot.element else "-")

	return table