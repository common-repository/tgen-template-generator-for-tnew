(function ($) {
	"use strict";

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	/**
	 * Reset Users
	 */

	// $( window ).load(function() {
	// });

	$(function () {
		$("#tgenGenerateTemplate").click(function (e) {
			tgenGenerateTemplate(e);
		});
	});
})(jQuery);

function tgenGenerateTemplate(e) {
	e.preventDefault();

	jQuery
		.ajax({
			url:
				tgentgApiSettings.root + "tgen-template-generator/v1/action/generate",
			type: "POST",
			contentType: "application/json",
			beforeSend: function (xhr) {
				xhr.setRequestHeader("X-WP-Nonce", tgentgApiSettings.tgentg_nonce);
				console.log("...");
				jQuery(".tgentg_generate_button").prop("disabled", true);
				jQuery(".tgentg_generate_button__message").html(
					"Script Running Please Wait..."
				);
			},
			data: JSON.stringify({
				oo: "var",
			}),
			success: function (response) {},
		})
		.done(function (results) {
			console.log("SUCCESS");
			console.log(results);
			console.log(results.data.urls.demo);
			jQuery(".tgentg_generate_button").prop("disabled", false);
			jQuery(".tgentg_generate_button__message").html(
				"Script Ran Successfully!"
			);

			jQuery(".tgentg_generate_button__preview").attr(
				"href",
				results.data.urls.demo
			);
			jQuery(".tgentg_generate_button__template").val(
				results.data.urls.template
			);
			jQuery(".tgentg_generate_button__response").removeClass("empty");
		})
		.fail(function (jqXHR, textStatus, errorThrown) {
			console.log("ERROR");
			console.log(jqXHR);
			console.log(textStatus);
			console.log(errorThrown);
			jQuery(".tgentg_generate_button").prop("disabled", false);
			jQuery(".tgentg_generate_button__message").html(
				"Script Failed. Error: " + errorThrown
			);
		});
}
