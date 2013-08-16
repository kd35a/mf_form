jQuery.webshims.polyfill();

function update_range_text(dom_obj)
{
	dom_obj.siblings('label').children('span').text(dom_obj.val());
}

jQuery(function($)
{
	$('form input[type=range]').each(function()
	{
		update_range_text($(this));
	});

	$('form input[type=range]').on('change', function()
	{
		update_range_text($(this));
	});
});
