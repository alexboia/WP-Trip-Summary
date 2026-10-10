from rich.console import Console
from rich.status import Status

WPTS_CONSOLE = Console()

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