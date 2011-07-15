(function($) {
	$('input.quickadd').live('keypress',function(e) {
		if (e.keyCode == 13) {
			var $this = $(this),
				$holder = $this.closest('div.quickAddHolder'),
				$options = $('ul.optionset',$holder),
				$inputs = $('input',$options),
				fieldName = $holder.children('div.quickadd:first').attr('id');
			$.ajax({
				beforeSend: function(XHR,settings) {
					if ($this.data('inProgress')) {
						return false;
					}
					$this.data('inProgress',true);
				},
				complete: function(XHR,textStatus) {
					$this.data('inProgress',false);
				},
				data: {'Title':$this.val()},
				dataType: 'json',
				error: function(XHR,textStatus,errorThrown) {
				},
				success: function(data,textStatus,XHR) {
					var $input = $inputs.filter('[value="' + data.ID + '"]');
					if ($input.length) {
						if (!$input.is(':checked')) {
							$input.click();
						}
					}
					else {
						if (!$inputs.length) {
							var inputType = $options.hasClass('checkboxsetfield') ? 'checkbox' : 'radio',
								$new = $('<li class="val' + data.ID + ' odd"><input type="' + inputType + '" id="' + $options.attr('id') + '_' + data.ID + '" name="' + fieldName + '[' + data.ID + ']' + '" value="' + data.ID + '" checked="checked" /><label for="' + $options.attr('id') + '_' + data.ID + '">' + data.Title + '</label></li>');
							$options.children().remove();
						}
						else {
							var $new = $options.children(':last').clone(),
								val = $new.val();
							$new.removeClass('val' + val).addClass('val' + data.ID);
							if ($new.hasClass('even')) {
								$new.removeClass('even').addClass('odd');
							}
							else {
								$new.addClass('even').removeClass('odd');
							}
							$new.children('input').attr('id',$options.attr('id') + '_' + data.ID).attr('name',fieldName + '[' + data.ID + ']').val(data.ID).attr('checked','checked');
							$new.children('label').attr('for',$options.attr('id') + '_' + data.ID).text(data.Title);
						}
						$options.append($new);
						$this.val('');
					}
				},
				url: $this.closest('form').attr('action') + '/field/' + fieldName + '/findOrAdd'
			});
		}
	});
})(jQuery)
