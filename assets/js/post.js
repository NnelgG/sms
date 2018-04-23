/**
 * File : post.js
 * This file contain the validation of post form
 * Using validation plugin : jquery.validate.js
 * ref: https://jqueryvalidation.org/remote-method/
 */

$(document).ready(function(){
	var postForm = $("#post");	//form id

	var validator = postForm.validate({
		rules:{
			message : { remote : { url : baseURL + "sms/validatePostMessage", type :"post" } },
			link : { remote : { url : baseURL + "sms/validatePostLink", type :"post", data: { link: function(){ return $("#link").val(); } } } },
			place : { remote : { url : baseURL + "sms/validatePostPlace", type :"post", data: { place_id: function(){ return $("#place_id").val(); } } } }
		},
		messages:{
			message : { remote : "" },
			link : { remote : "" },
			place : { remote : "" }
		}
	});
});
