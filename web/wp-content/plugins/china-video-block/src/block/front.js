$(document).ready(function () {
	if (window.location.href.indexOf('/events/kubecon-cloudnativecon-china-2018/') != -1) {
		var youku = $('#youku'),
			china = 'https://v.qq.com/iframe/player.html?vid=f0718z01vwl&tiny=0&auto=0',
			world = 'https://www.youtube.com/embed/1JAXMGqzMxs',
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

		function isNotChina() {
			youku.attr('src', world)

			$('.single-sponsor-icon a').each(function () {
				var link = $(this).data('link');

				if (link) {
					$(this).attr('href', link);
				}
			})
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
						isNotChina()
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
			isNotChina()
		}
	}
	if (window.location.href.indexOf('/events/kubecon-cloudnativecon-china-2019/') != -1) {
		var youku = $('#youku'),
			china = 'https://v.qq.com/x/page/j0851tovwnn.html',
			world = 'https://www.youtube.com/embed/GV-Q_-RPfJA',
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

		function isNotChina() {
			youku.attr('src', world)

			$('.single-sponsor-icon a').each(function () {
				var link = $(this).data('link');

				if (link) {
					$(this).attr('href', link);
				}
			})
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
						isNotChina()
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
			isNotChina()
		}
	}
});