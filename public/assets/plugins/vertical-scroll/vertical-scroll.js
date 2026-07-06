$(function () {
	$(".vertical-scroll").bootstrapNews({
		newsPerPage: 4,
		autoplay: true,
		pauseOnHover: true,
		navigation: false,
		direction: 'down',
		newsTickerInterval: 9000,
		onToDo: function () {
			//console.log(this);
		}
	});
	$(".vertical-scroll1").bootstrapNews({
		newsPerPage: 2,
		autoplay: true,
		pauseOnHover: true,
		navigation: false,
		direction: 'down',
		newsTickerInterval: 9000,
		onToDo: function () {
			//console.log(this);
		}
	});
});
