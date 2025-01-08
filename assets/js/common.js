var langData = [];
function selLang(element) {
    const selectedLang = $(element).val();

    localStorage.setItem('lang', selectedLang);

    loadLanguage(selectedLang);
}

function loadLanguage(lang) {
    $.ajax({
        url: '/common/lang_handler.php',
        type: 'POST',
        data: { lang: lang },
        dataType: 'json',
        success: function (data) {
			langData = data;
            $('[data-translate]').each(function () {
                const key = $(this).data('translate');
                if (data[key]) {
                    $(this).text(data[key]);
                }
            });
			document.querySelectorAll('[data-translate]').forEach(element => {
				const key = element.getAttribute('data-translate');
				const translatedText = data[key] || key;
				element.innerHTML = translatedText.replace(/\\n/g, '<br>');
			});
        },
        error: function () {
            console.error('Failed to load language data');
        }
    });
}

$(document).ready(function () {
    const selectedLang = localStorage.getItem('lang') || 'ko';
	$("#lang").val(selectedLang);
    loadLanguage(selectedLang);
});
