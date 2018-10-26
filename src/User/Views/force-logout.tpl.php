<?php
  // dump($_COOKIE['__ata__']);
  // dump($_SESSION);
?>
<html>
  <head>
    <script src="/assets/js/jquery.js"></script>
    <script src="/assets/js/bqfp.min.js"></script>
    <link href="/assets/css/font-awesome.min.css" rel="stylesheet" />    
    <style type="text/css">
      @import url(https://fonts.googleapis.com/css?family=Lato:400,300,300italic,400italic,600,600italic,700,700italic,800,800italic);
      body { 
        background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAYAAACpSkzOAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAxMC8yOS8xMiKqq3kAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzVxteM2AAABHklEQVRIib2Vyw6EIAxFW5idr///Qx9sfG3pLEyJ3tAwi5EmBqRo7vHawiEEERHS6x7MTMxMVv6+z3tPMUYSkfTM/R0fEaG2bbMv+Gc4nZzn+dN4HAcREa3r+hi3bcuu68jLskhVIlW073tWaYlQ9+F9IpqmSfq+fwskhdO/AwmUTJXrOuaRQNeRkOd5lq7rXmS5InmERKoER/QMvUAPlZDHcZRhGN4CSeGY+aHMqgcks5RrHv/eeh455x5KrMq2yHQdibDO6ncG/KZWL7M8xDyS1/MIO0NJqdULLS81X6/X6aR0nqBSJcPeZnlZrzN477NKURn2Nus8sjzmEII0TfMiyxUuxphVWjpJkbx0btUnshRihVv70Bv8ItXq6Asoi/ZiCbU6YgAAAABJRU5ErkJggg==);
      }
      .error-template {
        padding: 40px 15px;text-align: center;
      }
      .error-actions {
        margin-top:15px;margin-bottom:15px;
      }
      .error-actions .btn {
        margin-right:10px; 
      }
      .hyperlink {
        font-weight:bold;
        font-size: 18px;
        color:#225992;
        text-decoration: none;
      }
      body {color: #797979; font-family: 'Lato', sans-serif; padding: 0px !important; margin: 0px !important; font-size: 16px; }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="error-template">
              <h1><i class="fa fa-exclamation-triangle"></i> Oops!</h1>
              <div class="error-details">
                <span class="ldBar" style="font-size:20px;font-weight:bold;color:#cb5249;">You have been logged out due to inactivity.<br /><a href="/login" class="hyperlink">Click here</a> to login again.</span>
              </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>

<?php exit; ?>