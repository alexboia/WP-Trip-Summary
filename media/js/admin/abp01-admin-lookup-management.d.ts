/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 *	1. Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 * 	2. Redistributions in binary form must reproduce the above copyright notice, 
 *		this list of conditions and the following disclaimer in the documentation 
 *		and/or other materials provided with the distribution.
 *
 *	3. Neither the name of the copyright holder nor the names of its contributors 
 *		may be used to endorse or promote products derived from this software without 
 *		specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, 
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/// <reference types="jquery" />
/// <reference types="lodash" />

interface WpTripSummaryLookupManagementContext {
	ajaxBaseUrl: string|null;
	
	getLookupNonce: string|null;
	addLookupNonce: string|null;
	editLookupNonce: string|null;
	deleteLookupNonce: string|null;
	
	ajaxGetLookupAction: string|null;
	ajaxAddLookupAction: string|null;
	ajaxEditLookupAction: string|null;
	ajaxDeleteLookupAction: string|null;
}

interface WpTripSummaryCurrentLookupDataItemSelection {
	type: string;
	typeName: string;
	language: string;
	languageName: string;
	isDefaultLanguage: boolean;
}

interface WpTripSummaryLookupDataItem {
	id: number;
	type: string;
	defaultLabel: string;
	hasTranslation: boolean;
	label: string;
}

interface WpTripSummaryLookupListingResponse {
	success: boolean;
	message: string;
	items?: WpTripSummaryLookupDataItem[];
}

interface WpTripSummaryLookupDataItemSaveResponse {
	success: boolean;
	message: string;
	item?: WpTripSummaryLookupDataItem;
}

interface WpTripSummaryLookupDataItemDeleteResponse {
	success: boolean;
	message: string;
	requiresConfirmation?: boolean;
	confirmationNonce?:string;
}

interface WpTripSummaryCurrentLookupItemsMap {
	[key: number]: WpTripSummaryLookupDataItem;
}

declare enum WpTripSummaryLookupDataItemDeleteStage {
	InitialRequest = "_lookup_delete_initial_request",
	InUseConfirmation = "_lookup_delete_inuse_confirmation"
}

interface WpTripSummaryLookupDataItemDeleteFlow {
	stage: WpTripSummaryLookupDataItemDeleteStage;
	nonce: string;
}

interface Window {
	abp01_ajaxUrl: string|null;
	abp01_getLookupNonce: string|null;
	abp01_addLookupNonce: string|null;
	abp01_editLookupNonce: string|null;
	abp01_deleteLookupNonce: string|null;
	abp01_ajaxGetLookupAction: string|null;
	abp01_ajaxAddLookupAction: string|null;
	abp01_ajaxEditLookupAction: string|null;
	abp01_ajaxDeleteLookupAction: string|null;
	lodash: LoDashFacade;
}

interface LookupDeleteInfo {
	stage: string|null;
	nonce: string|null;
}