/// <reference types="jquery" />
/// <reference types="toastr" />

export {
	WpTripSummary
};

interface WpTripSummary {
	scrollToTop(): void;
	disableWindowScroll(): void;
	enableWindowScroll(): void;
	initToastMessages(target: any): void;
	toastMessage(success: boolean, message: string): void;
	initTooltipsOnPage(container: string): void;
}

interface WpTripSummaryAdminCommonL10N {
	lblConfirmQuestion: string;
	lblConfirmTitle: string;
	btnNo: string;
	btnYes: string;
}

interface WpTripSummarySettingsL10N {
	msgSaveWorking: string;
	msgSaveOk: string;

	errSaveFailNetwork: string;
	errSaveFailGeneric: string;
}

interface WpTripSummaryMaintenanceL10N {
	msgWorking: string;
	msgConfirmExecute: string;
	msgExecutedOk: string;
	msgExecutedFailGeneric: string;
	msgExecutedFailNetwork: string;
}

interface WpTripSummaryAdminSystemLogsL10N {
	msgLogFileRemovalSuccess: string;
	errCouldNotRemoveLogFile: string;
	msgConfirmLogFileRemoval: string;
	errCouldNotLoadLogFile: string;
	errCouldNotFindLogFile: string;
}

declare global {
	interface Window { 
		abp01: WpTripSummary; 
		abp01SettingsL10n: WpTripSummarySettingsL10N;
		abp01MaintenanceL10n: WpTripSummaryMaintenanceL10N;
		abp01AdminCommonL10n: WpTripSummaryAdminCommonL10N;
		abp01AdminSystemLogL10n: WpTripSummaryAdminSystemLogsL10N;
	}

	interface JQueryStatic {
		abp01: WpTripSummary;
	}
}