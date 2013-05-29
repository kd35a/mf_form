jQuery(function($)
{
	$('.ajax_link').on('click', function()
	{
		var type = $(this).attr('href').substring(1);

		if($(this).hasClass("confirm_link") && !confirm("Verkligen?"))
		{
			return false;
		}

		$.ajax(
		{
			url: '/wp-content/plugins/mf_form/include/ajax.php?type=' + type,
			type: 'get',
			dataType: 'json',
			success: function(data)
			{
				if(data.success)
				{
					if(data.dom_id)
					{
						$('#' + data.dom_id).remove();
					}
				}

				else
				{
					alert(data.error);
				}
			}
		});

		return false;
	});

	$('.ajax_checkbox').on('click', function()
	{
		var type = $(this).attr('rel');

		$.ajax(
		{
			url: '/wp-content/plugins/mf_form/include/ajax.php?type=' + type,
			type: 'get',
			dataType: 'json',
			success: function(data)
			{
				if(data.success)
				{}

				else
				{
					alert(data.error);
				}
			}
		});
	});
});