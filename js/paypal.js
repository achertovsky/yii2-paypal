$('#paypalSettingsSaveBtn').on('click', function(event) {
    console.log('prevent');
    event.preventDefault();
    var approve = confirm("Are you sure you want to save new settings?");
    if (approve == true) {
      	$('#paypalSettingsSaveForm').submit();
    }
});
