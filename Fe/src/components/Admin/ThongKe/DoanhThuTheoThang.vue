<template>
    <div class="row">
        <!-- Thanh Lọc Năm -->
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="input-group">
                                <span class="input-group-text fw-bold bg-primary text-white">Năm Báo Cáo</span>
                                <input v-model="nam_chon" type="number" min="2020" max="2030" class="form-control" placeholder="2026">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <button @click="loadData" class="btn btn-primary text-white w-100 fw-bold">
                                <i class="bx bx-filter-alt"></i> Xem Thống Kê
                            </button>
                        </div>
                        <div class="col-lg-3 text-end">
                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 py-2 px-3">
                                Tổng: {{ formatMoney(tong_ca_nam) }} đ
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row">
                <!-- Bảng Chi Tiết 12 Tháng -->
                <div class="col-lg-5 mb-3">
                    <div class="card shadow-sm h-100 border-top border-3 border-primary">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0 text-white fw-bold text-uppercase fs-6">
                                <i class="bx bx-list-ol"></i> Doanh Thu 12 Tháng (Năm {{ nam_chon }})
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive custom-scroll" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-bordered table-hover mb-0 align-middle">
                                    <thead class="table-light text-center sticky-top shadow-sm">
                                        <tr>
                                            <th>Tháng</th>
                                            <th>Số Đơn Hàng</th>
                                            <th>Doanh Thu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in list_data" :key="index">
                                            <td class="text-center fw-semibold">{{ item.thang_full }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-primary-subtle text-primary border rounded-pill">
                                                    {{ item.so_don_hang }} đơn
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold text-success">{{ formatMoney(item.tong_doanh_thu) }} đ</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biểu Đồ Doanh Thu Line Chart -->
                <div class="col-lg-7 mb-3">
                    <div class="card shadow-sm h-100 border-top border-3 border-primary">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0 text-white fw-bold text-uppercase fs-6">
                                <i class="bx bx-area"></i> Biểu Đồ Tăng Trưởng Doanh Thu
                            </h5>
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 380px;">
                            <div class="w-100" style="height: 350px;">
                                <Line v-if="is_load" :options="chartOptions" :data="chartData" />
                                <div v-else class="text-center text-muted py-5">
                                    <i class="bx bx-loader-alt bx-spin fs-1 d-block mb-2 text-primary"></i>
                                    Đang tải dữ liệu biểu đồ...
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
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, Filler } from 'chart.js';
import axios from 'axios';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, Filler);

export default {
    name: 'DoanhThuTheoThang',
    components: {
        Line
    },
    data() {
        return {
            nam_chon: 2026,
            list_data: [],
            is_load: false,
            chartData: {
                labels: [],
                datasets: [
                    {
                        label: 'Doanh Thu (VNĐ)',
                        backgroundColor: 'rgba(13, 110, 253, 0.15)',
                        borderColor: '#0d6efd',
                        pointBackgroundColor: '#0d6efd',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#0d6efd',
                        fill: true,
                        tension: 0.35,
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
    computed: {
        tong_ca_nam() {
            return this.list_data.reduce((sum, item) => sum + (Number(item.tong_doanh_thu) || 0), 0);
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
            axios.get(`http://127.0.0.1:8000/api/admin/thong-ke/doanh-thu-theo-thang?nam=${this.nam_chon}`)
                .then(res => {
                    if (res.data.status) {
                        this.list_data = res.data.data;
                        this.chartData.labels = this.list_data.map(i => i.thang);
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
