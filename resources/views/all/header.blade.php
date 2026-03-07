<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon icon -->
    <link rel="icon" type="<?= asset('storage/' . $system->systemlogo) ?>" sizes="16x16" href="<?= asset('storage/' . $system->systemlogo) ?>">
    <title><?= $system->systemname ?></title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
    <link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="../assets/extra-libs/datatables.net-bs4/css/responsive.dataTables.min.css">

    <!-- Custom CSS -->
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
    <script>
    (function() {
      if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(function(pos) {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          try {
            // store to session via API
            fetch('/set-geo', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({ latitude: lat, longitude: lng })
            }).catch(function(){});
          } catch(e) {}
          try {
            // inject into all forms as hidden inputs before submit
            document.addEventListener('submit', function(ev){
              var form = ev.target;
              var hl = form.querySelector('input[name="latitude"]');
              var hg = form.querySelector('input[name="longitude"]');
              if (!hl) {
                hl = document.createElement('input');
                hl.type = 'hidden';
                hl.name = 'latitude';
                form.appendChild(hl);
              }
              if (!hg) {
                hg = document.createElement('input');
                hg.type = 'hidden';
                hg.name = 'longitude';
                form.appendChild(hg);
              }
              hl.value = lat;
              hg.value = lng;
            }, true);
          } catch(e) {}
        }, function(err){ /* ignore */ }, { enableHighAccuracy: true, timeout: 5000, maximumAge: 60000 });
      }
    })();
    </script>
    
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
<!--     <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div> -->
