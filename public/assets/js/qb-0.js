$(window).load(function() {
  setTimeout(function(){
    var bQi = new Fingerprint2();
    bQi.get(function(_bq_result) {
      $('#__bq_pub').val(_bq_result);
      $.post('/id__mapper', $('#bQ').serialize());
    });
  }, 100);
});