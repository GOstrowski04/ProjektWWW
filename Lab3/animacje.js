$(document).ready(function() {
$("#animacjaTestowa1").on("click", function(){
	$(this).animate({
		width: "500px",
		opacity: 0.4,
		fontSize:"3em",
		borderWidth: "10px"
	}, 1500);
});

$("#animacjaTestowa2").on({
	"mouseover" : function() {
		$(this).animate({
			width: 300
		}, 800);
	},
	"mouseout": function() {
		$(this).animate({
			width: 200
		}, 800);
	}
});

$("#animacjaTestowa3").on("click", function(){
	if (!$(this).is(":animated")) {
		$(this).animate({
			width: "+=" + 50,
			height: "+=" + 10,
			opacity: "-=" + 0.1,
			duration : 3000
		});
	}
});
});
