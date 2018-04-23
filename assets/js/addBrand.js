/**
 * File : addBrand.js
 * 
 * This file contain the validation of add user form
 * 
 * Using validation plugin : jquery.validate.js
 * 
 * @author Kishor Mali
 */

$(document).ready(function(){
	
	var addBrandForm = $("#addBrand");
	
	var validator = addBrandForm.validate({
		
		rules:{
			about :{ required : true },
			brandName : { required : true, remote : { url : baseURL + "brand/checkBrandExists", type :"post"} },
			categoryId : { required : true, selected : true}
		},
		messages:{
			about :{ required : "This field is required" },
			brandName : { required : "This field is required", remote : "Brand already exist" },
			categoryId : { required : "This field is required", selected : "Please select atleast one option" }			
		}
	});
});
