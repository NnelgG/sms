/**
 * @author Kishor Mali
 */


jQuery(document).ready(function(){
	
	jQuery(document).on("click", ".deleteUser", function(){
		var userId = $(this).data("userid"),
			hitURL = baseURL + "deleteUser",
			currentRow = $(this);
		
		var confirmation = confirm("Are you sure to delete this user ?");
		
		if(confirmation)
		{
			jQuery.ajax({
			type : "POST",
			dataType : "json",
			url : hitURL,
			data : { userId : userId } 
			}).done(function(data){
				console.log(data);
				currentRow.parents('tr').remove();
				if(data.status = true) { alert("User successfully deleted"); }
				else if(data.status = false) { alert("User deletion failed"); }
				else { alert("Access denied..!"); }
			});
		}
	});

	jQuery(document).on("click", ".deleteSms", function(){
		var smsId = $(this).data("smsid"),
			hitURL = baseURL + "sms_rud/deleteSms",
			currentRow = $(this);
		
		var confirmation = confirm("Are you sure to delete this scheduled post ?");
		
		if(confirmation)
		{
			jQuery.ajax({
			type : "POST",
			dataType : "json",
			url : hitURL,
			data : { smsId : smsId } 
			}).done(function(data){
				console.log(data);
				currentRow.parents('tr').remove();
				if(data.status = true) { alert("Scheduled post successfully deleted"); }
				else if(data.status = false) { alert("Scheduled post deletion failed"); }
				else { alert("Access denied..!"); }
			});
		}
	});
	
	
	jQuery(document).on("click", ".searchList", function(){
		
	});
	
});
