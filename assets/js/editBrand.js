/**
 * File : editBrand.js 
 * 
 * This file contain the validation of edit brand form
 * 
 * @author
 */
$(document).ready(function(){
	
	var editBrandForm = $("#editBrand");
	
	var validator = editBrandForm.validate({
		
		rules:{
			brandName : { required : true, remote : { url : baseURL + "brand/CheckBrandExists", type :"post", data : { brandId : function(){ return $("#brandId").val(); } } } },
			about :{ required : true },
			categoryId : { required : true, selected : true}
		},
		messages:{
			brandName : { required : "This field is required", remote : "Brand already exist" },
			about :{ required : "This field is required" },
			categoryId : { required : "This field is required", selected : "Please select atleast one option" }			
		}
	});
});