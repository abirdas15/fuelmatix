<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Shift Sale</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Shift Sale Start</h4>
                        </div>
                        <div class="card-body">
                            <div class="process-wrapper">
                                <div id="progress-bar-container">
                                    <ul>
                                        <li class="step step01 active">
                                            <div class="step-inner">Octane</div>
                                        </li>
                                        <li class="step step02">
                                            <div class="step-inner">Petrol </div>
                                        </li>
                                        <li class="step step03">
                                            <div class="step-inner">LPG</div>
                                        </li>
                                        <li class="step step04">
                                            <div class="step-inner">CNG</div>
                                        </li>
                                        <li class="step step05">
                                            <div class="step-inner">Summary</div>
                                        </li>
                                    </ul>

                                    <div id="line">
                                        <div id="line-progress"></div>
                                    </div>
                                </div>

                                <div id="progress-content-section">
                                    <div class="section-content discovery active">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Octane</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Oll Stock </p>
                                                        </label>

                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="Previous Reading ">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="Final Reading">
                                                    </div>

                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Consumption ">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="Amount">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Dispenser-1</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 1">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 2">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Dispenser-2</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 1">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 2">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="section-content strategy">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Petrol</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Oll Stock </p>
                                                        </label>

                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="Previous Reading ">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="Final Reading">
                                                    </div>

                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Consumption ">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="Amount">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Dispenser-1</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 1">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 2">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Dispenser-2</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 1">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 2">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="section-content creative">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">LPG</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Oll Stock </p>
                                                        </label>

                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="Previous Reading ">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="Final Reading">
                                                    </div>

                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Consumption ">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="Amount">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Dispenser-1</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 1">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 2">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Dispenser-2</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 1">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 2">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="section-content production">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">CNG</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Oll Stock </p>
                                                        </label>

                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="Previous Reading ">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="Final Reading">
                                                    </div>

                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Consumption ">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="Amount">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Dispenser-1</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               placeholder="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 1">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 2">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Dispenser-2</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 1">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 2">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="section-content analysis">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Summary</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Oll Stock </p>
                                                        </label>

                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="Previous Reading ">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="Final Reading">
                                                    </div>

                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Consumption ">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="Amount">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Dispenser-1</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="intial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 1">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 2">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Dispenser-2</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 1">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">
                                                            <p>Nozzle 1</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="initial reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <input type="text" class="form-control"
                                                               value="End reading">
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <input type="text" class="form-control"
                                                               value="sale on nozzle 2">
                                                    </div>
                                                    <div class="mb-3 col-md-2">

                                                        <input type="text" class="form-control"
                                                               value="Amount ">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    mounted() {
        $('#dashboard_bar').text('Shift Sale Start')
    }
}
</script>

<style scoped>

</style>
