<?= $this->extend('adminlte') ?>

<?= $this->section('styles') ?>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
<?= $this->endSection() ?>

<?= $this->section('page_header');?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark"><?= esc($title)?></h1>
    </div><!-- /.col -->
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div><!-- /.col -->
</div>

<?= $this->endSection();?>

<?= $this->section('content') ?>

<?php if(!empty(session()->getFlashdata('failMsg'))):?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('failMsg');?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
<?php endif;?>
<?php if(!empty(session()->getFlashdata('successMsg'))):?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('successMsg');?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
<?php endif;?>
<div class="row">
  <div class="col-12 col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user"></i></span>

      <div class="info-box-content">
        <span class="info-box-text">Total Users</span>
        <span class="info-box-number">
					<?= esc($userCount)?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <div class="col-12 col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-danger elevation-1"><i class="ion ion-document"></i></span>

      <div class="info-box-content">
        <span class="info-box-text">Files</span>
        <span class="info-box-number">
					<?= esc($fileCount)?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <div class="col-12 col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-success elevation-1"><i class="ion ion-document"></i></span>

      <div class="info-box-content">
        <span class="info-box-text">Active Elections</span>
        <span class="info-box-number">
					<?= esc($activeElections['count'])?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <div class="col-12 col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-comments"></i></span>

      <div class="info-box-content">
        <span class="info-box-text">Discussions</span>
        <span class="info-box-number">
					<?= esc($discussions)?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
</div>

<div class="row">
  <!-- File count chart -->
  <div class="col-md-5">
    <div class="card">
      <div class="card-header">
        <p class="card-title">Total File Count</p>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-12">
            <canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Monthly chart -->
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <p class="card-title">File Upload for the Year</p>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <canvas id="bar-chart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection();?>

<?= $this->section('scripts') ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.2/raphael-min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.0/morris.min.js"></script>
  <!-- ChartJS -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.4.1/dist/chart.min.js"></script>

  <!-- chartJS bar chart -->
  <script>
    $(function(){
      //get the bar chart canvas
      var d = new Date();
      const monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
      const data = {
        labels: monthNames,
        datasets: [{
          label: 'Number of file uploads for the year '+d.getFullYear(),
          data: [<?php foreach($files as $file):?>
            <?= $file['Jan']?>,
            <?= $file['Feb']?>,
            <?= $file['Mar']?>,
            <?= $file['Apr']?>,
            <?= $file['May']?>,
            <?= $file['Jun']?>,
            <?= $file['Jul']?>,
            <?= $file['Aug']?>,
            <?= $file['Sep']?>,
            <?= $file['Oct']?>,
            <?= $file['Nov']?>,
            <?= $file['Dec']?>,
            <?php endforeach;?>
          ],
          borderColor: '#2596BE',
          tension: 0.1
        }]
      }
      const config = {
        type: 'line',
        data: data,
      };
      var chart = new Chart($("#bar-chart"), {
        type: "line",
        data: data,
        options: config
      });
    });
  </script>
  <!-- chartJS sample donut -->
  <script>
    var donutChart = $("#donutChart");
    var fileCats = JSON.parse(`<?= $fileCategories; ?>`);
    console.log(fileCats);
    var data1 = {
    labels: fileCats.label,
    datasets: [
      {
        data: fileCats.count,
        backgroundColor: [
          '#1A03A3',
          '#006BF8',
          '#01C0F8',
          '#DC00FE'
        ],
        hoverOffset: 4
      }
    ]
  };
  //options
  var options = {
    responsive: true,
    title: {
      display: true,
      position: "top",
      text: "Doughnut Chart",
      fontSize: 18,
      fontColor: "#111"
    },
    legend: {
      display: true,
      position: "top",
      labels: {
        fontColor: "#333",
        fontSize: 16
      }
    }
  };
  //create Chart class object
  var chart1 = new Chart(donutChart, {
    type: "doughnut",
    data: data1,
    options: options
  });
  </script>
<?= $this->endSection() ?>
