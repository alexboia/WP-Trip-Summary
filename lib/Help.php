<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit ;
}

class Abp01_Help {
    /**
     * @var Abp01_Env The current instance of the plug-in environment
     */
    private $_env;

    private $_fallbackLocale = 'default';

    public function __construct() {
        $this->_env = Abp01_Env::getInstance();
    }

    private function _isLocaleCodeValid($locale) {
        return !empty($locale) 
            && (preg_match('/^([a-zA-Z0-9]{2,})(_([a-zA-Z0-9]{2,})){0,}$/', $locale) === 1 
                || $locale == $this->_fallbackLocale);
    }

    /**
     * Retrieve the absolute help file path that corresponds to the given locale
     * 
     * @param string $locale The locale to compute the path for
     * @return string The absolute help file path
     */
    private function _getHelpFilePathForLocale($locale) {
        return sprintf('%s/help/%s/index.html', 
            $this->_env->getDataDir(), 
            $locale);
    }

    public function hasHelpFileForLocale($locale) {
        return $this->_isLocaleCodeValid($locale) 
            && is_readable($this->_getHelpFilePathForLocale($locale));
    }

    public function getLocalesWithHelpContents() {
        $localesWithHelp = array();
        $systemLocales = Abp01_Locale::getSystemLocales();
        $currentLocale = Abp01_Locale::getCurrentLocale();

        foreach ($systemLocales as $key => $label) {
            if ($this->hasHelpFileForLocale($key)) {
                $isCurrent = $key === $currentLocale;
                if ($isCurrent) {
                    $label = sprintf('%s - %s', $label, __('Current language', 'abp01-trip-summary'));
                }
                $localesWithHelp[$key] = array(
                    'label' => $label,
                    'isCurrent' => $isCurrent
                );
            }
        }

        return $localesWithHelp;
    }

    /**
     * Retrives the help contents that corresponds to the given locale.
     * If no contents is found, the contents for the default locale is returned.
     * 
     * @param string $locale The locale to return the contents for
     * @return string The contents
     */
    public function getHelpContentForLocale($locale) {
        if (empty($locale)) {
            throw new InvalidArgumentException('Locale cannot be empty');
        }

        if (!$this->_isLocaleCodeValid($locale)) {
            throw new InvalidArgumentException('Locale code <' . $locale . '> is not valid');
        }

        $helpFile = $this->_getHelpFilePathForLocale($locale);
	
        if (!is_file($helpFile) || !is_readable($helpFile)) {
            $helpFile = $this->_getHelpFilePathForLocale($this->_fallbackLocale);
            $locale = $this->_fallbackLocale;
        }

        if (is_file($helpFile) && is_readable($helpFile)) {
            $contents = file_get_contents($helpFile);

            $helpDataDirUrl = $this->_env->getPluginAssetUrl(sprintf('data/help/%s', 
                $locale));

            $contents =  str_ireplace('$helpDataDirUrl$', 
                $helpDataDirUrl, 
                $contents);
        } else {
            $contents = null;
        }

        return $contents;
    }

    /**
     * Retrives the help contents that corresponds to the current locale.
     * If no contents is found, the contents for the default locale is returned.
     * 
     * @return string The contents
     */
    public function getHelpContentForCurrentLocale() {
        $currentLocale = Abp01_Locale::getCurrentLocale();
        return $this->getHelpContentForLocale($currentLocale);
    }
}