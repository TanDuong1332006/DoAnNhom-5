<template>
    <div class="row">
        <!-- Bộ lọc năm -->
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="row align-items-center g-2">
                        <div class="col-lg-5">
                            <div class="input-group">
                                <span class="input-group-text fw-bold bg-success text-white">Từ năm</span>
                                <input v-model="tu_nam" type="number" min="2020" max="2030" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="input-group">
                                <span class="input-group-text fw-bold bg-success text-white">Đến năm</span>
                                <input v-model="den_nam" type="number" min="2020" max="2030" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <button @click="loadData" class="btn btn-success text-white w-100 fw-bold">
                                <i class="bx bx-filter"></i> Lọc Dữ Liệu
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row">
                <!-- Bảng Số Liệu -->
                <div class="col-lg-5 mb-3">
                    <div class="card shadow-sm h-100 border-top border-3 border-success">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="mb-0 text-white fw-bold text-uppercase fs-6">
                                <i class="bx bx-table"></i> Bảng Doanh Thu Theo Năm
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-hover mb-0 align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>STT</th>
                                        <th>Năm</th>
                                        <th>Tổng Doanh Thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in list_data" :key="index">
                                        <td class="text-center">{{ index + 1 }}</td>
                                        <td class="text-center fw-bold">Năm {{ item.nam }}</td>
                                        <td class="text-end fw-bold text-success">{{ formatMoney(item.tong_doanh_thu) }} đ</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Biểu Đồ Cột Bar Chart -->
                <div class="col-lg-7 mb-3">
                    <div class="card shadow-sm h-100 border-top border-3 border-success">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="mb-0 text-white fw-bold text-uppercase fs-6">
                                <i class="bx bx-bar-chart-alt-2"></i> Biểu Đồ Cột So Sánh Theo Năm
                            </h5>
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 380px;">
                            <div class="w-100" style="height: 350px;">
                                <Bar v-if="is_load" :options="chartOptions" :data="chartData" />
                                <div v-else class="text-center text-muted py-5">
                                    <i class="bx bx-loader-alt bx-spin fs-1 d-block mb-2 text-success"></i>
                                    Đang tải biểu đồ...
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
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import axios from 'axios';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

export default {
    name: 'DoanhThuTheoNam',
    components: {
        Bar
    },
    data() {
        return {
            tu_nam: 2022,
            den_nam: 2026,
            list_data: [],
            is_load: false,
            chartData: {
                labels: [],
                datasets: [
                    {
                        label: 'Doanh Thu (VNĐ)',
                        backgroundColor: '#198754',
                        borderRadius: 6,
                        data: []
                    }
                ]
            },
            chartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Doanh thu: ' + Number(context.raw).toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) {
                                return (val / 1000).toLocaleString('vi-VN') + 'k';
                            }
                        }
                    }
                }
            }
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        formatMoney(val) {
            if (!val) return '0';
            return Number(val).toLocaleString('vi-VN');
        },
        loadData() {
            this.is_load = false;
            axios.get(`http://127.0.0.1:8000/api/admin/thong-ke/doanh-thu-theo-nam?tu_nam=${this.tu_nam}&den_nam=${this.den_nam}`)
                .then(res => {
                    if (res.data.status) {
                        this.list_data = res.data.data;
                        this.chartData.labels = this.list_data.map(i => 'Năm ' + i.nam);
                        this.chartData.datasets[0].data = this.list_data.map(i => i.tong_doanh_thu);
                        this.is_load = true;
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.is_load = true;
                });
        }
    }
}
</script>
