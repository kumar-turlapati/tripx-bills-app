<?php
  // dump($_COOKIE['__ata__']);
  // dump($_SESSION);
?>
<html>
<head>
  <script src="/assets/js/jquery.js"></script>
  <script src="/assets/js/bqfp.min.js"></script>
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
  <script type="text/javascript">
    $(window).load(function() {
      setTimeout(function(){
        var bQi = new Fingerprint2();
        bQi.get(function(_bq_result) {
          $('#__bq_pub').text(_bq_result);
        });
      }, 50);
    });
  </script>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="error-template">
            <h1>Oops!</h1>
            <h2>Device not accepted for Security reasons.</h2>
            <div class="error-details">We are unable to authorize your device with name<br />
            <span class="ldBar" style="font-size:30px;font-weight:bold;color:#cb5249;text-decoration:underline;" id="__bq_pub"></span>
            <br />Send the above device name to your Administrator for accessing this application further. 
            <br />Without white-listing your device, you can not proceed.
            <br /><br /><h4>Please close this window and <a href="/login" class="hyperlink">login</a> again when your device is accepted by your administrator.</h4></div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

<?php exit; ?>