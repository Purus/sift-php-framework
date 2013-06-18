/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Czech translations for FileUploader
 *
 * @param {jQuery} $
 * @returns {void}
 * @requires FileUploader
 */
(function($)
{
  $.extend($.fn.fileUploader.defaults.messages, {
    uploadedBytes: 'Nahrávané byty překračují velikost souboru.',
    maxNumberOfFiles: 'Maximální počet nahrávaných souborů byl dosažen.',
    acceptFileTypes: 'Typ souboru není dovolen.',
    maxFileSize: 'Soubor je příliš velký.',
    minFileSize: 'Soubor je příliš malý.',
    // custom messages
    browseFiles: 'Procházet soubory...',
    cancel: 'zrušit',
    notAvailable: 'n/a',
    error: 'Chyba při nahrávání.',
    uploaded: 'Úspěšně nahráno.',
    aborted: 'Nahrávání zrušeno.',
    uploadStarted: 'Probíhá nahrávání, prosím čekejte...',
    uploadInQueue: 'Soubor ve frontě pro nahrání.'
  });

}(window.jQuery));