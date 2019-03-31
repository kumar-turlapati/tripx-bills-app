$(window).load(function() {
  $(".se-pre-con").fadeOut("slow");
  setTimeout(function(){
    if($('#bQ').length > 0) {
      var bQi = new Fingerprint2();
      bQi.get(function(_bq_result) {
        $('#__bq_pub').val(_bq_result);
        $.post('/id__mapper', $('#bQ').serialize());
      });
    }
  }, 100);  
  if( $('#sendOtpBtn').length>0 ) {
    $('#sendOtpBtn').on('click', function(){
      sendOTP();
    });
    $('#submit-fp').on('click', function(e){
      var userId = $('#emailID').val();
      var otp = $('#pass-fp').val();
      var newPassword = $('#newpass-fp').val();
      if(userId === '' || otp === '' || newPassword === '') {
        alert('Userid, OTP and New password fields are mandatory to Reset your password.');
        $('#emailID').focus();
        return false;
      }
      /* hit server to reset the password */
      jQuery.ajax("/reset-password", {
        method:"POST",
        data: $('#forgotPassword').serialize(),
        success: function(response) {
          if(response.status===false) {
            alert(response.error);
            window.location.href = '/forgot-password';
          } else {
            alert('Password has been changed successfully.');
            window.location.href = '/login';
          }
        },
        error: function(e) {
          alert('Unable to reset password. Please try again.');
          window.location.href = '/forgot-password';
        }
      });

      e.preventDefault();
    });
  }
});

function sendOTP(fpType) {
  var userId = $('#emailID').val();
  var emailFilter = /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$/;
  if(!emailFilter.test(userId)) {
    $('#emailID').focus();
    alert('Please enter a valid username.');
    return false;
  }

  /* hit server to get the OTP */
  jQuery.ajax("/send-otp", {
    method:"POST",
    data: $('#forgotPassword').serialize(),
    success: function(response) {
      if(response.status===false) {
        alert(response.errortext);
        return false;
      }
      if(response.status === true) {
        $('#success-msg-fp').show();
        $('#success-msg-fp').html(response.message);
        $('#pass-fp').attr('disabled', false);
        $('#submit-fp').attr('disabled', false);
        $('#newpass-fp').attr('disabled', false);
        $('#sendOtpBtn').attr('disabled', true);
        if(fpType==='resend') {
          alert('OTP has been resent successfully. Please use latest code to reset your password.');
        }
      } else {
        $('#error-msg-fp').show();
        $('#error-msg-fp').html(response.message);
        if(fpType==='resend') {
          alert('Unable to resend OTP.');
        }        
      }
    },
    error: function(e) {
      $('#emailID').focus();
      alert('An error occurred while processing your request.');
      return false;
    }
  });
}