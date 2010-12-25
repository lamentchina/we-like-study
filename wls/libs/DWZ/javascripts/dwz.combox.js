/**
 * @author Roger Wu
 */
var allSelectBox = [];
(function($){
	$.extend($.fn, {
		selectBox: function(options){
			var op = $.extend({
				selector: ">a",
				styleClass: "selected"
			}, options);
			return this.each(function(){
				$(">li", $(this)).select(op);
			});
		},
		select: function(options){
			var op = $.extend({
				selector: ">a",
				styleClass: "selected"
			}, options);
			var killAllBox = function(bObj){
				$.each(allSelectBox, function(i){
					if (allSelectBox[i] != bObj) {
						$(allSelectBox[i]).removeClass(op.styleClass);
					}
				});
			}
			return this.each(function(){
				var box = $(this);
				var selector = $(op.selector, box);
				box.data("title", selector.text());
				allSelectBox.push(box);
				var options = $(">ul", box);
				$(op.selector, box).click(function(){
					if (options.is(":hidden")) {
						$(box).addClass(op.styleClass);
						killAllBox(box);
						$(document).click(killAllBox);
					}
					else {
						$(document).unbind("click", killAllBox);
						killAllBox();
					}
					return false;
				});
				$(box).append("<input type='hidden' name='" + selector.attr("name") + "' value='"+selector.attr("value")+"'/>");
				$(">li", options).option(selector, op.styleClass, box);
			});
		},
		option: function(selector, sClass, box){
			selector.text(box.data("title"));
			var property = $("input[name=" + selector.attr("name") + "]", box);
			property.attr("value", "");
			return this.each(function(){
				$(">a", this).click(function(){
					selector.text($(this).text());
					property = $("input[name=" + selector.attr("name") + "]", box);
					if (property.val() != $(this).attr("value")) {
						var change = eval(selector.attr("change"));
						if ($.isFunction(change)) {
							var options = change($(this).attr("value"));
							var html = "";
							for (var i = 0; i < options.length; i++) {
								html += "<li><a href=\"#\" value=\"" + options[i][0] + "\">" + options[i][1] + "</a></li>";
							}
							var rel = box.attr("rel");
							var relObj = $(".combox>li[name=" + rel + "]");
							options = $(">ul", relObj);
							options.html(html);
							$(">li", options).option($(">a", relObj), sClass, relObj);
						}
					}
					property.attr("value", $(this).attr("value"));
					$(box).removeClass(sClass);
				});
			});
			box.removeData("title");
		},
		combox:function(){
			return this.each(function(){
				var $this = $(this);
				var name = $this.attr("name");
				var value=$this.val();
				var label = $("option[value=" + value + "]",$this).text();
				var ref = $this.attr("ref");
				var html = "<div class=\"searchbar\"><ul class=\"combox\"><li rel=\"" + ref + "\" name=\"" + name + "\">";
				html += "<a href=\"#\" name=\"" + name +"\" value=\"" + value + "\" change=\"" + $this.attr("change")+ "\">" + label +"</a><ul>"
				$("option", $this).each(function(){
					var option = $(this);
					html +="<li><a href=\"#\" value=\"" + option[0].value + "\">" + option[0].text + "</a></li>";
				});
				html += "</ul></li></ul></div>";
				$this.after(html);
				$("ul.combox", $this.next()).selectBox();
				$this.remove();
			});
		}
	});
})(jQuery);
