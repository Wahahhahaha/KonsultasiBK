        <div class="page-wrapper">
            <div class="page-breadcrumb">

        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Database Backup</h3>
                    <p class="text-subtitle text-muted">Export and import database in MySQL format.</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/home">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Database</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Export Database</h5>
                    </div>
                    <div class="card-body">
                        <p>Download current database as MySQL .sql file.</p>
                        <a href="/database/export" class="btn btn-primary">
                            Export .sql
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Import Database</h5>
                    </div>
                    <div class="card-body">
                        <p>Upload a .sql backup file to update database data.</p>
                        <form action="/database/import" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="backup_file" class="form-label">Backup file (.sql)</label>
                                <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql" required>
                            </div>
                            <button type="submit" class="btn btn-danger">
                                Import .sql
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if(session('success')) { ?>
            <div class="alert alert-success mt-3"><?= session('success') ?></div>
        <?php } ?>

        <?php if(session('error')) { ?>
            <div class="alert alert-danger mt-3"><?= session('error') ?></div>
        <?php } ?>
    </section>
</div>

