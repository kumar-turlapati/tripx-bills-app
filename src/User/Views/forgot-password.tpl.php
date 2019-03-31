<form method="POST" autocomplete="off" id="forgotPassword">
	<div class="forgot-bg">
		<div class="forgot-form">
			<div class="forgot-wrap">
				<div class="container-fluid">
	      		<div class="row">
	            <h2 class="text-center">
	            	<i class="fa fa-key password-icon" aria-hidden="true"></i> Forgot Password
	            </h2>
	            <hr />
	            <div class="row">
	               <div class="col-xs-12 col-sm-12 col-md-8">
	                  <div class="form-group">
	                     <div class="input-group">
	                        <input 
	                        	placeholder="Username (ex: joe@example.com)" 
	                        	name="emailID" 
	                        	id="emailID"
	                        	class="form-control" 
	                        	type="email" 
	                        	autofocus 
	                        />
	                        <span class="error-msg" style="display:none;" id="error-msg-fp"></span>
	                        <span class="sucess-msg" style="display:none;" id="success-msg-fp"></span>
	                     </div>
	                  </div>
	               </div>
	               <div class="col-md-4">
	               	<button type="button" class="btn btn-primary" id="sendOtpBtn">
	               		<i class="fa fa-mobile mobile-icon" aria-hidden="true"></i> Send OTP
	               	</button>
	               	<span class="resend">
	               		<a href="javascript:void(0);" onclick="sendOTP('resend')">Resend OTP</a>
	               	</span>
	               </div>
	            </div>
	            <div class="row">
	               <div class="col-xs-12 col-sm-12 col-md-4">
	                  <div class="form-group">
	                     <div class="input-group">
	                        <input name="pass" class="form-control" type="text" placeholder="OTP" id="pass-fp" name="otp" disabled />
	                     </div>
	                  </div>
	               </div>
	            </div>
	            <div class="row">
	               <div class="col-xs-12 col-sm-12 col-md-8">
	                  <div class="form-group">
	                     <div class="input-group">
	                        <input 
	                        	name="password" 
	                        	class="form-control" 
	                        	type="password" 
	                        	placeholder="New password" 
	                        	id="newpass-fp" 
	                        	disabled 
	                        />
	                     </div>
	                  </div>
	               </div>
	            </div>    
	            <hr />
	            <div class="row">
	            	<div class="col-xs-12 col-sm-12 col-md-12">
	              	<button type="submit" class="btn btn-primary btn-lg btn-block" id="submit-fp" disabled> Submit </button>
	              </div>
	            </div>
	      	</div>
	   		</div>
			</div>
		</div>
	</div>
</form>