/// <reference types="jquery" />
/// <reference types="toastr" />

export {};

interface WpTripSummary {
	scrollToTop(): void;
	disableWindowScroll(): void;
	enableWindowScroll(): void;
	initToastMessages(target: any): void;
	toastMessage(success: boolean, message: string): void;
}

declare global {
	interface Window { 
		abp01: WpTripSummary; 
	}

	interface JQueryStatic {
		abp01: WpTripSummary
	}
}