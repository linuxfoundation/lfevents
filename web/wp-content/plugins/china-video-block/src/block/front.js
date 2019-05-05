$(document).ready(function () {
	var youku = $('.wp-block-lfe-block-china-video-block iframe'),
		china = chinavid,
		world = worldvid,
		path = "path=/;"
	d = new Date();
	d.setTime(d.getTime() + (7 * 24 * 60 * 60 * 1000)),
		expires = "expires=" + d.toUTCString();

	function getCookie(name) {
		var value = "; " + document.cookie;
		var parts = value.split("; " + name + "=");
		if (parts.length == 2) return parts.pop().split(";").shift();
		else return "";
	}

	if (getCookie("is_china") === "") {
		$.ajax({
			url: "https://ipinfo.io?token=d590ecd654d57b",
			dataType: "jsonp",
			success: function (response) {
				if (response.country == 'CN') {
					youku.attr('src', china)
					document.cookie = "is_china=true;" + path + expires;
				} else {
					youku.attr('src', world)
					document.cookie = "is_china=false;" + path + expires;
				}
			},
			error: function () {
				youku.attr('src', china)
				document.cookie = "is_china=true;" + path + expires;
			},
			timeout: 3000
		});
	} else if (getCookie("is_china") === "true") {
		youku.attr('src', china)
	} else {
		youku.attr('src', world)
	}	
});