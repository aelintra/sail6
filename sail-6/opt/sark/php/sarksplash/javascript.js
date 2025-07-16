 $('#chantable').dataTable ( {
    "bPaginate": false,
    "bAutoWidth": true,
    "sDom": 't',
    "bSort" : false
  } );   

  // New: update system stats as plain text
  function updateSystemText(obj) {

    console.log('updateSystemText obj:', obj);
  

    // System resources
    let sysText = '<i>';
    if (obj.numCpus !== undefined) sysText += 'CPU(s): ' + obj.numCpus + ',';
    if (obj.mem !== undefined) sysText += ' Mem:  ' + obj.mem + '%,';
    if (obj.disk !== undefined) sysText += ' Disk: ' + obj.disk + '%,';
    if (obj.iowait !== undefined) sysText += ' I/O Wait: ' + obj.iowait + '%,';

    let ldavgText = ' Load Average:';
    if (obj.lga !== undefined) ldavgText += ' ' + obj.lga + ',';
    if (obj.lgb !== undefined) ldavgText += ' ' + obj.lgb + ',';
    if (obj.lgc !== undefined) ldavgText += ' ' + obj.lgc + '</i>';
    ldavgText += '</i>';
  
    $('#sys_div').html(sysText + ldavgText);

  }

  function updateChans() {
    $.get('ajaxchannels.php',
      function (response) {
        $('#chantable').html(response);
    });
  }

  function doSystem() {
    console.log('doSystem');

    $.ajax({
      url: 'system.php',
      success: function (response) {
        var obj = JSON.parse(response);
        updateSystemText(obj);
        var upcalls = document.getElementById('upcalls');
        upcalls.innerHTML = obj.upcalls;
      }
    });
  }

  function doData() {
    console.log('doData');

    $.ajax({
      url: 'cdrcount.php',
      success: function (response) {
        var dataobj = JSON.parse(response);
        var inbound = document.getElementById('inbound');
        inbound.innerHTML = dataobj.inbound;
        var outbound = document.getElementById('outbound');
        outbound.innerHTML = dataobj.outbound;
        var internal = document.getElementById('internal');
        internal.innerHTML = dataobj.internal;
      }
    });
  }

  function doEndpoints() {    
    console.log('doEndpoints');

    $.ajax({
      url: 'endpoints.php',
      success: function (response) {
        var dataobj = JSON.parse(response);
        var extensions = document.getElementById('extensions');
        extensions.innerHTML = dataobj.phoneUpCount + '/' + dataobj.phoneCount;
        var trunks = document.getElementById('trunks');
        trunks.innerHTML = dataobj.trunkUpCount + '/' + dataobj.trunkCount;
      }
    });
  }

  // Initial load
  updateChans();
  doSystem();
  doData();
  //doEndpoints();

  // Set intervals for updates
  setInterval(function() {
    updateChans();
  }, 5000);
  setInterval(function() {
    doSystem();
  }, 5000);
  setInterval(function() {
    doData();
  }, 10000);
  /*
  setInterval(function() {
    doEndpoints();
  }, 60000);
  */