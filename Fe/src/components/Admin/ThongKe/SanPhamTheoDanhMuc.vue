<template>
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <h5 class="mb-0 text-dark fw-bold text-uppercase fs-6">
                        <i class="bx bx-pie-chart-alt-2 text-warning me-1"></i> Phân Bổ Sản Phẩm Theo Danh Mục
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row">
                <!-- Bảng Danh Mục & Số Sản Phẩm -->
                <div class="col-lg-5 mb-3">
                    <div class="card shadow-sm h-100 border-top border-3 border-warning">
                        <div class="card-header bg-warning text-dark py-3">
                            <h5 class="mb-0 fw-bold text-uppercase fs-6">
                                <i class="bx bx-list-check"></i> Số Lượng Sản Phẩm Mỗi Danh Mục
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-hover mb-0 align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên Danh Mục</th>
                                        <th>Số Sản Phẩm</th>
                                        <th>Tỷ Lệ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in list_data" :key="index">
                                        <td class="text-center">{{ index + 1 }}</td>
                                        <td class="fw-semibold text-dark">{{ item.ten_danh_muc }}</td>
                                        <td class="text-center fw-bold text-primary">{{ item.san_phams_count }} SP</td>
                                        <td class="text-center small text-muted">
                                            {{ tong_san_pham > 0 ? ((item.san_phams_count / tong_san_pham) * 100).toFixed(1) + '%' : '0%' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Doughnut Chart -->
                <div class="col-lg-7 mb-3">
                    <div class="card shadow-sm h-100 border-top border-3 border-warning">
                        <div class="card-header bg-warning text-dark py-3">
                            <h5 class="mb-0 fw-bold text-uppercase fs-6">
                                <i class="bx bx-doughnut-chart"></i> Biểu Đồ Tròn Cơ Cấu Sản Phẩm
                            </h5>
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 380px;">
                            <div class="w-100" style="height: 350px;">
                                <Doughnut v-if="is_load" :options="chartOptions" :data="chartData" />
                                <div v-else class="text-center text-muted py-5">
                                    <i class="bx bx-loader-alt bx-spin fs-1 d-block mb-2 text-warning"></i>
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
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import axios from 'axios';

ChartJS.register(Title, Tooltip, Legend, ArcElement);

export default {
    name: 'SanPhamTheoDanhMuc',
    components: {
        Doughnut
    },
    data() {
        return {
            list_data: [],
            is_load: false,
            chartData: {
                labels: [],
                datasets: [
                    {
                        backgroundColor: [
                            '#0d6efd', '#6610f2', '#6f42c1', '#d63384',
                            '#dc3545', '#fd7e14', '#ffc107', '#198754',
                            '#20c997', '#0dcaf0'
                        ],
                        data: []
                    }
                ]
            },
            chartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        }
    },
    computed: {
        tong_san_pham() {
            return this.list_data.reduce((sum, item) => sum + (Number(item.san_phams_count) || 0), 0);
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        loadData() {
            this.is_load = false;
            axios.get('http://127.0.0.1:8000/api/admin/thong-ke/san-pham-theo-danh-muc')
                .then(res => {
                    if (res.data.status) {
                        this.list_data = res.data.data;
                        this.chartData.labels = this.list_data.map(i => i.ten_danh_muc);
                        this.chartData.datasets[0].data = this.list_data.map(i => i.san_phams_count);
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
