/// <reference types="jquery" />
/// <reference types="toastr" />

export {
	WpTripSummary,
	WpTripSummaryBusyToggler
};

declare type WpTripSummaryBusyToggler = (show: boolean, message?: string) => void;
declare type WpTripSummaryKiteJS = {
	formatters: any;
	(template: string, data?: any): any
};

interface WpTripSummary {
	scrollToTop(): void;
	disableWindowScroll(): void;
	enableWindowScroll(): void;
	initToastMessages(target: any): void;
	toastMessage(success: boolean, message: string): void;
	initTooltipsOnPage(container: string): void;
	createBusyToggler(selector: string, defaultMessage?: string): WpTripSummaryBusyToggler;
	isNullOrWhiteSpace(value: any): boolean;
	kiteTemplate(templateId: string, data?: any): any;
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
	msgWorking: string;
	msgLogFileRemovalSuccess: string;
	errCouldNotRemoveLogFile: string;
	msgConfirmLogFileRemoval: string;
	errCouldNotLoadLogFile: string;
	errCouldNotFindLogFile: string;
}

interface WpTripSummaryLookupManagementL10N {
	msgWorking: string;
	errListingFailGeneric: string;
	errListingFailNetwork: string;
	errFailGeneric: string;
	errFailNetwork: string;
	msgSaveOk: string;
	errSaveFailInvalidData: string;
	ttlConfirmDelete: string;
}

declare global {
	type WpTripSummaryBusyToggler = (show: boolean, message?: string) => void;

	interface Window { 
		abp01: WpTripSummary; 
		abp01SettingsL10n: WpTripSummarySettingsL10N;
		abp01MaintenanceL10n: WpTripSummaryMaintenanceL10N;
		abp01AdminCommonL10n: WpTripSummaryAdminCommonL10N;
		abp01AdminSystemLogL10n: WpTripSummaryAdminSystemLogsL10N;
		abp01LookupMgmtL10n: WpTripSummaryLookupManagementL10N;
		kite: WpTripSummaryKiteJS;
	}

	interface JQueryStatic {
		abp01: WpTripSummary;
	}

	interface JQuery {
		singleVal(): string;
		singleValNumeric(defaultValue?: number): number;
		optionTextByValue(value: string): string;
	}
}