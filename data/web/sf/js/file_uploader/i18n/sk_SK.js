/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Slovak translations for FileUploader
 *
 * @param {jQuery} $
 * @returns {void}
 * @requires FileUploader
 */
(function($)
{
  $.extend($.fn.fileUploader.defaults.messages, {
    uploadedBytes: 'Nahrávané byty prekračujú veľkosť súboru.',
    maxNumberOfFiles: 'Dosiahli ste maximálny počet nahrávaných súborov.',
    acceptFileTypes: 'Tento typ súboru nie je podporovaný.',
    maxFileSize: 'Súbor je príliš veľký.',
    minFileSize: 'Súbor je príliš malý.',
    browseFiles: 'Prechádzať súbory...',
    cancel: 'zrušiť',
    notAvailable: 'n/a',
    error: 'Chyba pri nahrávaní.',
    uploaded: 'Úspešne nahrané.',
    aborted: 'Nahrávanie bolo zrušené.',
    uploadStarted: 'Súbory sa nahrávajú, prosím vyčkajte...',
    uploadInQueue: 'Čaká sa na nahrávanie súboru.'
  });

}(window.jQuery));