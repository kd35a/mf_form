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

	$('form .form_zipcode input').each(function()
	{
		$(this).after("<span></span>");
	});

	$('form .form_zipcode input').on('focusout', function()
	{
		var dom_obj = $(this),
			search = dom_obj.val();

		$.ajax(
		{
			url: '/wp-content/plugins/mf_form/include/ajax.php?type=zipcode/search/' + search,
			type: 'get',
			dataType: 'json',
			success: function(data)
			{
				if(data.success)
				{
					dom_obj.siblings('span').text(data.response);		
				}				
			}
		});
	});
});
