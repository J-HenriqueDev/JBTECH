@extends('layouts.layoutMaster')

@section('title', 'Dashboard Fiscal')

@section('vendor-script')
    @vite(['resources/assets/js/charts-apex.js'])
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 text-primary" style="font-size: 2rem; font-weight: bold;">
            <i class="bx bx-pulse me-2"></i> Saúde do Motor Fiscal
        </h1>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-md rounded bg-primary bg-opacity-10 me-3">
                        <i class="bx bx-search text-primary fs-3 m-2"></i>
                    </div>
                    <div>
                        <h4 class="mb-0" id="health-notas">{{ $health['notasDetectadas'] }}</h4>
                        <small class="text-muted">Notas Detectadas (Mês)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-md rounded bg-success bg-opacity-10 me-3">
                        <i class="bx bx-file text-success fs-3 m-2"></i>
                    </div>
                    <div>
                        <h4 class="mb-0" id="health-xmls">{{ $health['xmlsCompletos'] }}</h4>
                        <small class="text-muted">XMLs Completos (Mês)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-md rounded bg-warning bg-opacity-10 me-3">
                        <i class="bx bx-time-five text-warning fs-3 m-2"></i>
                    </div>
                    <div>
                        <h4 class="mb-0" id="health-pendentes">{{ $health['processamentoPendente'] }}</h4>
                        <small class="text-muted">Processamento Pendente</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    @if ($health['sonecaMinutos'] > 0)
                        <span class="badge bg-label-info me-3">
                            💤 Robô em espera técnica. Retorno em {{ $health['sonecaMinutos'] }} min.
                        </span>
                    @else
                        <span class="badge bg-label-success me-3">
                            🚀 Motor Fiscal Ativo
                        </span>
                    @endif
                    <small class="text-muted">{{ $health['statusMessage'] }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Resumo Fiscal</h5>
                </div>
                <div class="card-body">
                    <div id="barChart"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Status</h5>
                </div>
                <div class="card-body">
                    <div id="donutChart"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Simple charts with current month stats
                const notas = Number(document.getElementById('health-notas').innerText) || 0;
                const xmls = Number(document.getElementById('health-xmls').innerText) || 0;
                const pend = Number(document.getElementById('health-pendentes').innerText) || 0;

                // Bar chart (ApexCharts)
                if (window.ApexCharts && document.querySelector('#barChart')) {
                    const chart = new ApexCharts(document.querySelector('#barChart'), {
                        chart: {
                            type: 'bar',
                            height: 320,
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                            name: 'Documentos',
                            data: [notas, xmls, pend]
                        }],
                        colors: ['#696cff', '#28c76f', '#ff9f43'],
                        plotOptions: {
                            bar: {
                                columnWidth: '45%',
                                borderRadius: 6
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        xaxis: {
                            categories: ['Detectadas', 'XMLs', 'Pendentes']
                        }
                    });
                    chart.render();
                }

                // Donut chart (ApexCharts)
                if (window.ApexCharts && document.querySelector('#donutChart')) {
                    const chart = new ApexCharts(document.querySelector('#donutChart'), {
                        chart: {
                            type: 'donut',
                            height: 320,
                            toolbar: {
                                show: false
                            }
                        },
                        series: [xmls, notas - xmls, pend],
                        labels: ['Completas', 'Detectadas (sem XML)', 'Pendentes'],
                        colors: ['#28c76f', '#696cff', '#ff9f43'],
                        dataLabels: {
                            enabled: true
                        },
                        legend: {
                            position: 'bottom'
                        }
                    });
                    chart.render();
                }
            });
        </script>
    @endpush
@endsection
