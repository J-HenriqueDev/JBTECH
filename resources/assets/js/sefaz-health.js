/**
 * SEFAZ Health Check - Global Polling
 * Updates Dashboard Widgets and Sidebar Badges
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get Base URL from HTML attribute
    const baseUrl = document.documentElement.getAttribute('data-base-url');
    const healthUrl = `${baseUrl}/admin/fiscal/health`;

    function checkSefazHealth() {
        fetch(healthUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            updateDashboardWidgets(data);
            updateSidebarBadges(data);
        })
        .catch(error => console.error('Erro ao verificar status SEFAZ:', error));
    }

    function updateDashboardWidgets(data) {
        // Update Stats on Dashboard / Index Page
        const elNotas = document.getElementById('stat-notas-detectadas');
        const elXmls = document.getElementById('stat-xmls-completos');
        const elPendente = document.getElementById('stat-processamento-pendente');

        if(elNotas) elNotas.innerText = data.notasDetectadas;
        if(elXmls) elXmls.innerText = data.xmlsCompletos;
        if(elPendente) elPendente.innerText = data.processamentoPendente;

        // Update Big Badges on Index Page
        const badgeSoneca = document.getElementById('badge-soneca');
        const badgeAtivo = document.getElementById('badge-ativo');
        const timer = document.getElementById('soneca-timer');

        if (data.sonecaMinutos > 0) {
            if(badgeSoneca) badgeSoneca.style.display = 'flex';
            if(badgeAtivo) badgeAtivo.style.display = 'none';
            if(timer) timer.innerText = data.sonecaMinutos;
        } else {
            if(badgeSoneca) badgeSoneca.style.display = 'none';
            if(badgeAtivo) badgeAtivo.style.display = 'flex';
        }
    }

    function updateSidebarBadges(data) {
        // Update Sidebar Badges
        const sbSoneca = document.getElementById('sidebar-sefaz-soneca-badge');
        const sbAtivo = document.getElementById('sidebar-sefaz-badge');
        const sbTimer = document.getElementById('sidebar-soneca-timer');

        if (data.sonecaMinutos > 0) {
            if(sbSoneca) {
                sbSoneca.style.display = 'inline-flex';
                // Ensure display is inline-flex for badge alignment
            }
            if(sbAtivo) sbAtivo.style.display = 'none';
            if(sbTimer) sbTimer.innerText = data.sonecaMinutos + 'm';
        } else {
            if(sbSoneca) sbSoneca.style.display = 'none';
            if(sbAtivo) sbAtivo.style.display = 'inline-flex';
        }
    }

    // Initial check
    checkSefazHealth();

    // Poll every 60 seconds
    setInterval(checkSefazHealth, 60000);
});
