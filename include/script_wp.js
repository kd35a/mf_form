function show_query_settings(this_val)
{
	jQuery('.tr_range, .tr_check, .tr_text, .tr_select').hide();

	if(this_val != '')
	{
		if(this_val == 2)
		{
			jQuery('.tr_range').show();
		}

		if(this_val == 3)
		{
			jQuery('.tr_check').show();
		}

		if(this_val != 6)
		{
			jQuery('.tr_text').show();
		}

		if(this_val == 10 || this_val == 11)
		{
			jQuery('.tr_select').show();
		}
	}
}

jQuery(function($)
{
	$('.ajax_link').on('click', function()
	{
		var type = $(this).attr('href').substring(1);

		if($(this).hasClass("confirm_link") && !confirm("Are you sure?"))
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

	show_query_settings($('#intQueryTypeID').val());

	$('#intQueryTypeID').on('change', function()
	{
		show_query_settings($(this).val());
	});

	$('.select_rows').on('blur', 'input', function()
	{
		var select_value = "",
			has_empty_id = false,
			i = 1;

		$('.select_rows > div').each(function()
		{
			var temp_id = $(this).children('.form_textfield').children('input[name=strQueryTypeSelect_id]').val() + "",
				temp_value = $(this).children('.form_textfield').children('input[name=strQueryTypeSelect_value]').val() + "";

			if(temp_id != "" || temp_value != "")
			{
				if(temp_id == "")
				{
					if(has_empty_id == true)
					{
						temp_id = "auto_" + i++;
					}

					else
					{
						has_empty_id = true;
					}
				}

				select_value += (select_value != '' ? "," : "") + temp_id + "|" + temp_value;
			}
		});

		$('.tr_select input[name=strQueryTypeSelect]').val(select_value);
	});

	$('.tr_select .icon-plus-sign').on('click', function()
	{
		var dom_content = $('.select_rows > div:last-child').html();

		$('.select_rows').append("<div>" + dom_content + "</div>");
	});

	$('.mf_form').sortable(
	{
		//handle: $('.form_row'),
		opacity: .7,
		//placeholder: 'ui-state-highlight',
		update: function()
		{
			var post_data = $(this).sortable('toArray');

			//console.log(post_data);

			$.ajax(
			{
				url: '/wp-content/plugins/mf_form/include/ajax.php?type=sortOrder',
				type: 'post',
				data: 'strOrder=' + post_data,
				dataType: 'json',
				success: function(data)
				{
					if(data.success)
					{}

					else if(data.error)
					{
						alert(data.error);
					}
				}
			});
		}
	});
});