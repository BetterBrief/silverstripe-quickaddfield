(function($) {
	$quickAddInput = $('input.quickadd');
	function getURL($obj,fieldName,action) {
		var formActionArray = $obj.closest('form').attr('action').split('?'),
			formAction = formActionArray[0],
			qryString = formActionArray[1] ? '?' + formActionArray[1] : '';
		return formAction + '/field/' + fieldName + '/' + action + qryString;
	}
	$('a.quickadd').live('click',function() {
		var $this = $(this),
			$holder = $this.closest('div.quickAddHolder'),
			$options = $('ul.optionset',$holder),
			$inputs = $('input',$options),
			fieldName = $holder.children('div.quickadd:first').attr('id'),
			$quickAddInput = $this.parent().prev().find('input.quickadd'),
			inputType = $options.hasClass('checkboxsetfield') ? 'checkbox' : 'radio';
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
			data: {'Title':$quickAddInput.val()},
			dataType: 'json',
			error: function(XHR,textStatus,errorThrown) {
			},
			success: function(data,textStatus,XHR) {
				if (data) {
					var $input = $inputs.filter('[value="' + data.ID + '"]'),
						name = inputType == 'radio' ? fieldName : fieldName + '[' + data.ID + ']';
					if ($input.length) {
						if (!$input.is(':checked')) {
							$input.click();
						}
					}
					else {
						if (!$inputs.length) {
							var $new = $('<li class="val' + data.ID + ' odd"><input type="' + inputType + '" id="' + $options.attr('id') + '_' + data.ID + '" name="' + name + '" value="' + data.ID + '" checked="checked" /><label for="' + $options.attr('id') + '_' + data.ID + '">' + data.Title + '</label></li>');
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
							$new.children('a').remove();
							$new.children('input').attr('id',$options.attr('id') + '_' + data.ID).attr('name',name).val(data.ID).attr('checked','checked');
							$new.children('label').attr('for',$options.attr('id') + '_' + data.ID).text(data.Title);
						}
						$options.append($new);
					}
					$quickAddInput.val('');
				}
			},
			url: getURL($this,fieldName,'findOrAdd')
		});
		return false;
	});
	$quickAddInput.live('keypress',function(e) {
		if (e.keyCode == 13) {
			$(this).parent().next().find('a.quickadd').click();
			e.preventDefault();
		}
	});
	$('div.quickAddHolder a.selectAll').live('click',function() {
		$('input:not(:checked)',$(this).siblings('ul.optionset')).click();
		return false;
	});
	$('.quickadd li').live('hover',function() {
		var $this = $(this),
			$input = $this.children('input'),
			fieldName = $this.closest('div.quickAddHolder').children('div.quickadd:first').attr('id');
		if ($this[0].className && !$this.children('a').length) {
			var $edit = $('<a class="edit" href="#">Edit</a>'),
				$delete = $('<a class="delete" href="#">Delete</a>');
			$delete.click(function() {
				if (confirm('Are you sure you want to delete this from items?')) {
					$.ajax({
						beforeSend: function(XHR,settings) {
							if ($delete.data('inProgress')) {
								return false;
							}
							$delete.data('inProgress',true);
						},
						complete: function(XHR,textStatus) {
							$delete.data('inProgress',false);
						},
						data: {'Id': $input.val()},
						dataType: 'json',
						error: function(XHR,textStatus,errorThrown) {
						},
						success: function(data,textStatus,XHR) {
							if (data.success == 1) {
								$this.fadeOut('slow',function() {
									$this.remove();
								});
							}
						},
						url: getURL($this,fieldName,'delete')
					});
				}
				return false;
			});
			$edit.click(function() {
				var $label = $input.siblings('label'),
					$editLabel = $('<input class="inlineEdit" value="' + $label.html() + '">');
				$editLabel.blur(function() {
					if ($editLabel.val() && $label.html() != $editLabel.val()) {
						//$label.html($editLabel.val());
						$.ajax({
							beforeSend: function(XHR,settings) {
								if ($edit.data('inProgress')) {
									return false;
								}
								$edit.data('inProgress',true).addClass('loading');
								$editLabel.attr('disabled','disabled');
							},
							complete: function(XHR,textStatus) {
								$edit.data('inProgress',false).removeClass('loading');
							},
							data: {
								'Id': $input.val(),
								'Title': $editLabel.val()
							},
							dataType: 'json',
							error: function(XHR,textStatus,errorThrown) {
							},
							success: function(data,textStatus,XHR) {
								if (data.success == 1) {
									$label.html(data.Title);
									$editLabel.replaceWith($label);
								}
								else {
									$editLabel.attr('disabled','').val($label.text()).focus();
								}
							},
							url: getURL($this,fieldName,'edit')
						});
					}
					else {
						$editLabel.replaceWith($label);
					}
				}).keypress(function(e) {
					if (e.keyCode == 13) {
						$editLabel.blur();
						e.preventDefault();
					};
				});
				$(document).keyup(function(e) {
					if (e.keyCode == 27) {
						$editLabel.replaceWith($label);
					}
				})
				$label.replaceWith($editLabel);
				$editLabel.focus();
				return false;
			});
			$this.append($edit).append($delete);
		}
		else {
			var $edit = $this.children('a.edit'),
				$delete = $this.children('a.delete');
		}
	});
})(jQuery)
