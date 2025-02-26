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

interface WpTripSummarySettingsL10N {
	msgSaveWorking: string;
	msgSaveOk: string;

	errSaveFailNetwork: string;
	errSaveFailGeneric: string;
}

declare global {
	interface Window { 
		abp01: WpTripSummary; 
		abp01SettingsL10n: WpTripSummarySettingsL10N;
	}

	interface JQueryStatic {
		abp01: WpTripSummary;
	}
}