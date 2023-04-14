<template>
    <div class="content-body">
        <!-- row -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="row">
                        <div class="col-xl-6 left">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-6 col-sm-6 left-left">
                                            <div class="col">
                                                <div class="card bg-success widget-stat">
                                                    <div class="card-body px-4 pb-4">
                                                        <div class="media">
                                <span class="me-3">
                                  <i class="fa fa-phone"></i>
                                </span>
                                                            <div class="media-body text-white text-end">
                                                                <p class="mb-1">Calls in <br> queue</p>
                                                                <h3 class="text-white">76</h3>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="card-header border-0 pb-0">
                                                        <h4 class="fs-20 font-w700 text-white">
                                                            Calls per hour
                                                        </h4>

                                                    </div>
                                                    <div class="card-body pb-0">
                                                        <div id="revenueMap" class="revenueMap"></div>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                        <div class="col-xl-6 col-sm-6 left-right">
                                            <div class="col">
                                                <div class="card bg-warning widget-stat">
                                                    <div class="card-body px-4 pb-4">
                                                        <div class="media">
                                <span class="me-3">
                                  <i class="fa fa-phone-volume"></i>
                                </span>
                                                            <div class="media-body text-white text-end">
                                                                <p class="mb-1">Ongoing <br> Calls</p>
                                                                <h3 class="text-white">76</h3>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card bg-success widget-stat">
                                                    <div class="card-body px-4 pb-4">
                                                        <div class="media">
                                <span class="me-3">
                                  <i class="fa-solid fa-check"></i>
                                </span>
                                                            <div class="media-body text-white text-end">
                                                                <p class="mb-1"> Agents <br> online</p>
                                                                <h3 class="text-white">76</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card bg-danger widget-stat">
                                                    <div class="card-body px-4 pb-4">
                                                        <div class="media">
                                <span class="me-3">
                                  <i class="fa-solid fa-phone-slash"></i>
                                </span>
                                                            <div class="media-body text-white text-end">
                                                                <p class="mb-1"> Abandoned calls</p>
                                                                <h3 class="text-white">76</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <!-- <div class="card bg-secondary">
                                              <div class="card-body px-4 pb-0">
                                                <div class="row">
                                                  <div class="col-xl-6">
                                                    <h4 class="fs-24 font-w600 mb-4 text-nowrap text-center text-white">
                                                      Average wait time
                                                    </h4>
                                                    <div class="d-flex justify-content-center">
                                                      <h2 class="fs-32 font-w700 mb-4 text-primary">0.00min</h2>
                                                    </div>
                                                  </div>
                                                  <div class="col-xl-6">
                                                    <h4 class="fs-24 font-w600 mb-4 text-nowrap text-center text-white">
                                                      Longest wait time
                                                    </h4>
                                                    <div class="d-flex justify-content-center">
                                                      <h2 class="fs-32 font-w700 mb-4 text-primary">0.0min</h2>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            </div> -->
                                            <div class="card overflow-hidden">
                                                <div class="social-graph-wrapper widget-facebook bg-info">
                                                    <span class="s-icon"><i class="fas fa-clock"></i></span> <span class="fs-24">wait
                              time</span>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 border-end">
                                                        <div class="pt-3 pb-3 ps-0 pe-0 text-center">
                                                            <h4 class="m-1"><span class="counter">0.00</span>min</h4>
                                                            <p class="m-0">average</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="pt-3 pb-3 ps-0 pe-0 text-center">
                                                            <h4 class="m-1"><span class="counter">0.00</span> min</h4>
                                                            <p class="m-0">longest</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 right">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-6 col-sm-6 right-left">
                                            <div class="col">
                                                <div class="card bg-info widget-stat">
                                                    <div class="card-body px-4 pb-4">
                                                        <div class="media">
                                <span class="me-3">
                                  <i class="fas fa-ticket-alt"></i>
                                </span>
                                                            <div class="media-body text-white text-end">
                                                                <p class="mb-1">Unassigned Tickets</p>
                                                                <h3 class="text-white">76</h3>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="card-header border-0 pb-0">
                                                        <h4 class="fs-20 font-w700 text-white">
                                                            Tickets per hour
                                                        </h4>

                                                    </div>
                                                    <div class="card-body pb-0">
                                                        <div id="revenueMap1" class="revenueMap">

                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                        <div class="col-xl-6 col-sm-6 right-right">
                                            <div class="col">
                                                <div class="card bg-secondary widget-stat">
                                                    <div class="card-body px-4 pb-4">
                                                        <div class="media">
                                <span class="me-3">
                                  <i class="fas fa-ticket-alt"></i>
                                </span>
                                                            <div class="media-body text-white text-end">
                                                                <p class="mb-1">Non Answered Tickets</p>
                                                                <h3 class="text-white">76</h3>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card bg-warning widget-stat">
                                                    <div class="card-body px-4 pb-4">
                                                        <div class="media">
                                <span class="me-3">
                                  <i class="fa-solid fa-microchip"></i>
                                </span>
                                                            <div class="media-body text-white text-end">
                                                                <p class="mb-1">Processing tickets</p>
                                                                <h3 class="text-white">76</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--<div class="card bg-danger">
                                                  <div class="card-body px-4 pb-0">
                                                    <div>
                                                      <h4 class="fs-24 font-w600 mb-4 text-nowrap text-center text-white">
                                                        Satisfaction(today)
                                                      </h4>
                                                      <div class="d-flex justify-content-center">
                                                        <h2 class="fs-32 font-w700 mb-4 text-white">100.0%</h2>

                                                      </div>
                                                    </div>
                                                  </div>
                                                </div> -->
                                                <!-- For pie charts-->

                                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 ">
                                                    <div class="card R-pie">
                                                        <div class="card-body">
                                                            <div class="chart-point">
                                                                <div class="check-point-area">
                                                                    <canvas id="doughnut_chart"></canvas>
                                                                </div>
                                                                <ul class="chart-point-list">
                                                                    <li><i class="fa fa-circle text-primary me-1"></i> Call </li>
                                                                    <li><i class="fa fa-circle text-success me-1"></i> Whatsapp</li>
                                                                    <li><i class="fa fa-circle text-warning me-1"></i> Sms</li>
                                                                    <li><i class="fa fa-circle text-email me-1"></i> Email</li>
                                                                    <li><i class="fa fa-circle text-mess me-1"></i> Messenger</li>

                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-12">

                                            <!-- <div class="card">
                                              <div class="card-body px-4 pb-0">
                                                <div class="row">
                                                  <div class="col-xl-6">
                                                    <h4 class="fs-24 font-w600 mb-4 text-nowrap text-center">
                                                      First reply time
                                                    </h4>
                                                    <div class="d-flex justify-content-center">
                                                      <h2 class="fs-32 font-w700 mb-4">0</h2>
                                                    </div>
                                                  </div>
                                                  <div class="col-xl-6">
                                                    <h4 class="fs-24 font-w600 mb-4 text-nowrap text-center">
                                                      Avg resolution time
                                                    </h4>
                                                    <div class="d-flex justify-content-center">
                                                      <h2 class="fs-32 font-w700 mb-4">158</h2>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            </div> -->


                                            <div class="card overflow-hidden">
                                                <div class="social-graph-wrapper widget-facebook bg-secondary">
                                                    <span class="s-icon"><i class="fas fa-clock"></i></span> <span class="fs-24">reply
                              time</span>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 border-end">
                                                        <div class="pt-3 pb-3 ps-0 pe-0 text-center">
                                                            <h4 class="m-1"><span class="counter">0.00</span>min</h4>
                                                            <p class="m-0">first</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="pt-3 pb-3 ps-0 pe-0 text-center">
                                                            <h4 class="m-1"><span class="counter">0.00</span> min</h4>
                                                            <p class="m-0">avg resolution</p>
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
    </div>
</template>

<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    name: "Dashboard"
}
</script>

<style scoped>

</style>
