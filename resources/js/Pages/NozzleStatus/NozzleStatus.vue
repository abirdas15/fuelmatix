<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Nozzle Status</a></li>

                </ol>
            </div>

            <!-- row -->
            <div class="card">
                <div class="card-title text-center mt-2">Dispenser 1</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="row">
                                <div class="col-sm-1"></div>
                                <div class="col-sm-10">
                                    <div class="amount mb-2">
                                        <div class="price d-flex flex-row justify-content-between mb-2">
                                            <div>P</div>
                                            <div>10000</div>
                                        </div>
                                        <div class="liter d-flex flex-row justify-content-between mb-2">
                                            <div>L</div>
                                            <div>1.2</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2 d-flex align-items-center">
                                <div class="col-sm-1">
                                    <img src="/images/gas.png">
                                </div>
                                <div class="col-sm-10">
                                    <div class="total-liter">10000</div>
                                </div>
                            </div>
                            <div class="row mb-2 d-flex align-items-center">
                                <div class="col-sm-1">
                                    <img src="/images/money.png">
                                </div>
                                <div class="col-sm-10">
                                    <div class="total-amount">10000</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="dispenser-container">
                                <div class="nozzle-left">
                                    <img src="/images/nozzle_left_fueling.png" alt="Left Nozzle">
                                </div>
                                <div class="dispenser-image">
                                    <img src="/images/img_dispenser_both_active.png" alt="Dispenser">
                                </div>
                                <div class="nozzle-right">
                                    <img src="/images/nozzle_right_active.png" alt="Right Nozzle">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="row">
                                <div class="col-sm-10">
                                    <div class="amount mb-2">
                                        <div class="price d-flex flex-row justify-content-between mb-2">
                                            <div>P</div>
                                            <div>10000</div>
                                        </div>
                                        <div class="liter d-flex flex-row justify-content-between mb-2">
                                            <div>L</div>
                                            <div>1.2</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-1"></div>
                            </div>
                            <div class="row mb-2 d-flex align-items-center">
                                <div class="col-sm-10">
                                    <div class="total-liter">10000</div>
                                </div>
                                <div class="col-sm-1 ps-0">
                                    <img src="/images/gas.png">
                                </div>
                            </div>
                            <div class="row mb-2 d-flex align-items-center">
                                <div class="col-sm-10">
                                    <div class="total-amount">10000</div>
                                </div>
                                <div class="col-sm-1 ps-0">
                                    <img src="/images/money.png">
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
import mqtt from 'mqtt/mqtt';
export default {
    data() {
        return {
            dispensers: [],
            client: null,
            brokerUrl: 'mqtt://fmagik.infrmtx.com',
            topic: 'infrmtx/fmagik/TX/0CB815F5F6D4/#',
            port: 1883
        }
    },
    created() {
        this.connect();
    },
    methods: {
        connect() {
            const options = {
                clientId: '1b3089ee-92f5-495d-9bdb-b8405ab5ba9b',
                port: 2000, // Default MQTT port
                protocol: 'mqtt', // Specify the protocol explicitly
                keepalive: 60,
                reconnectPeriod: 1000,
                connectTimeout: 30 * 1000,
                clean: true,
                resubscribe: true,
                defaultProtocol: 'mqtt'
            };
            this.client = mqtt.connect(this.brokerUrl, options);
            console.log(this.client);

            this.client.on('connect', () => {
                console.log('Connected to MQTT broker');
                this.client.subscribe(this.topic);
            });

            this.client.on('message', (topic, message) => {
                console.log('Received message:', topic, message.toString());
                // Handle incoming message as needed
            });
        },
        publishMessage(message) {
            this.client.publish(this.topic, message);
        }
    }
}
</script>

<style lang="scss">
.amount {
    width: 100%;
    height: 120px;
    background-color: #BDCFD7;
    font-family: 'Digital Numbers', sans-serif;
    font-weight: bold;
    padding: 10px;
    font-size: 30px;
}
.total-liter, .total-amount {
    width: 100%;
    height: 50px;
    background-color: #CDD2E1;
    padding: 3px;
    text-align: center;
    font-size: 30px;
}
.total-amount {
    width: 100%;
    height: 50px;
    background-color: #68D8D6;
    padding: 3px;
    text-align: center;
    font-size: 30px;
}
.dispenser-container {
    position: relative;
    width: fit-content;
    margin: auto;
}

.nozzle-left {
    position: absolute;
    left: -62px;
    top: 50px;
    img {
        height: 150px;
    }
}

.nozzle-right {
    position: absolute;
    right: -62px;
    top: 50px;
    img {
        height: 150px;
    }
}

.dispenser-image img {
    display: block;
    width: 160px;
}

</style>
