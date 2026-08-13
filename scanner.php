<?php
// scanner.php - Live Scanner IP dengan Update Status Only & Live Progress Batch
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_type'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    $action = $_POST['action_type'];

    // 1. Aksi untuk Menjalankan Scan per Batch (Real-Time Progress)
    if ($action == 'run_batch_scan') {
        set_time_limit(30);
        ini_set('max_execution_time', '30');

        $subnet_id = isset($_POST['subnet_id']) ? intval($_POST['subnet_id']) : 0;
        $subnet_block = isset($_POST['subnet_block']) ? trim($_POST['subnet_block']) : '';
        $start_ip = isset($_POST['start_ip']) ? intval($_POST['start_ip']) : 1;
        $batch_size = 30; 
        $end_ip = min(254, $start_ip + $batch_size - 1);

        if (empty($subnet_block)) {
            echo json_encode(['status' => 'error', 'message' => 'Subnet block tidak valid.']);
            exit;
        }

        $base_ip = explode('/', $subnet_block)[0];
        $parts = explode('.', $base_ip);
        if (count($parts) < 3) {
            echo json_encode(['status' => 'error', 'message' => 'Format subnet tidak dikenali.']);
            exit;
        }
        $prefix = "{$parts[0]}.{$parts[1]}.{$parts[2]}";

        // Ambil data database untuk subnet ini
        $db_ips = [];
        if (isset($conn) && $subnet_id > 0) {
            $q_db = $conn->query("SELECT ip_address, description FROM ip_addresses WHERE subnet_id = $subnet_id");
            if ($q_db && $q_db->num_rows > 0) {
                while ($row = $q_db->fetch_assoc()) {
                    $db_ips[$row['ip_address']] = !empty($row['description']) ? $row['description'] : '';
                }
            }
        }

        $batch_results = [];

        for ($i = $start_ip; $i <= $end_ip; $i++) {
            $ip = "{$prefix}.{$i}";
            
            // Ping cepat dengan timeout 0.25 detik
            $ping_cmd = sprintf("ping -c 1 -W 0.25 %s > /dev/null 2>&1; echo $?", escapeshellarg($ip));
            $output = [];
            exec($ping_cmd, $output);
            
            $status_code = isset($output[0]) ? intval($output[0]) : 1;
            $is_online = ($status_code === 0);

            $in_db = isset($db_ips[$ip]);
            // Ambil keterangan murni dari database, jika tidak ada kosongkan ('')
            $description = $in_db ? $db_ips[$ip] : '';

            $batch_results[] = [
                'ip' => $ip,
                'status' => $is_online ? 'Online' : 'Offline',
                'description' => $description,
                'in_db' => $in_db
            ];
        }

        $next_start = ($end_ip < 254) ? $end_ip + 1 : null;

        echo json_encode([
            'status' => 'success',
            'data' => $batch_results,
            'next_start' => $next_start,
            'progress_percent' => round(($end_ip / 254) * 100)
        ]);
        exit;
    }

    // 2. Aksi untuk Menyimpan / Meng-update ke Database (Hanya Update Status / Insert IP Baru tanpa menimpa deskripsi lama)
    if ($action == 'save_scan_results') {
        $subnet_id = isset($_POST['subnet_id']) ? intval($_POST['subnet_id']) : 0;
        $scanned_data = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];

        if ($subnet_id > 0 && !empty($scanned_data)) {
            foreach ($scanned_data as $item) {
                $ip = $conn->real_escape_string($item['ip']);
                $status = $conn->real_escape_string($item['status']);
                $desc = $conn->real_escape_string($item['description']);

                $check = $conn->query("SELECT id FROM ip_addresses WHERE subnet_id = $subnet_id AND ip_address = '$ip'");
                if ($check && $check->num_rows > 0) {
                    // PENTING: Hanya UPDATE status-nya saja, kolom description dibiarkan tetap (tidak ditimpa)
                    $conn->query("UPDATE ip_addresses SET status = '$status' WHERE subnet_id = $subnet_id AND ip_address = '$ip'");
                } else {
                    // Jika IP belum ada di database, masukkan dengan status hasil scan dan deskripsi kosong/bawaan
                    $conn->query("INSERT INTO ip_addresses (subnet_id, ip_address, status, description) VALUES ($subnet_id, '$ip', '$status', '$desc')");
                }
            }
            echo json_encode(['status' => 'success', 'message' => 'Status berhasil disinkronisasi ke database!']);
            exit;
        }
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyinkronkan data.']);
        exit;
    }
}
?>

<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-slate-800/95 text-cyan-400 rounded-xl border border-slate-700/80 shadow-sm">
                <i class="fa-solid fa-radar text-xl animate-pulse"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white tracking-wide">Live Scanner IP</h2>
                <p class="text-slate-400 text-sm">Pemindaian jaringan real-time interaktif dengan progress langsung per blok /24.</p>
            </div>
        </div>
    </div>

    <div class="bg-[#111827] border border-slate-800 rounded-xl p-6 shadow-xl">
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-800">
            <div>
                <h3 class="text-base font-bold text-white tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-cyan-400"></i> Real-Time Live Scanner (/24)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Pilih target block subnet jaringan di bawah ini untuk memulai live scanning.</p>
            </div>
            <span class="text-xs font-mono text-cyan-400 bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700">CONTROL PANEL</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-end">
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Pilih Block Subnet</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-solid fa-network-wired"></i>
                    </div>
                    <select id="subnetTarget" class="w-full bg-[#090d16] border border-slate-800 rounded-lg pl-10 pr-4 py-3 text-white focus:outline-none focus:border-cyan-500 text-sm font-mono transition duration-150">
                        <option value="">-- Pilih Block Subnet (Beserta Organisasi & AS Number) --</option>
                        <?php
                        if (isset($conn)) {
                            $q_sub = $conn->query("SELECT b.id, b.subnet_block, o.name as org_name, a.as_number FROM block_subnets b LEFT JOIN organizations o ON b.organization_id = o.id LEFT JOIN as_numbers a ON b.as_number_id = a.id ORDER BY b.id DESC");
                            if ($q_sub && $q_sub->num_rows > 0) {
                                while ($sub = $q_sub->fetch_assoc()) {
                                    $orgText = $sub['org_name'] ? " | " . $sub['org_name'] : "";
                                    $asText = $sub['as_number'] ? " (" . $sub['as_number'] . ")" : "";
                                    echo "<option value='{$sub['id']}' data-block='{$sub['subnet_block']}'>{$sub['subnet_block']}{$asText}{$orgText}</option>";
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div>
                <button onclick="startLiveScan()" id="scanButton" class="w-full px-5 py-3 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-semibold rounded-lg text-sm shadow-md transition duration-200 flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-play text-xs"></i> Mulai Live Scan (/24)
                </button>
            </div>
        </div>

        <div id="scanProgressContainer" class="mt-6 hidden">
            <div class="flex justify-between text-xs text-slate-400 mb-1.5 font-mono">
                <span id="scanStatusText" class="text-cyan-400">Memulai pemindaian real-time...</span>
                <span id="scanPercentage">0%</span>
            </div>
            <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-700/50">
                <div id="scanProgressBar" class="bg-cyan-400 h-2 rounded-full transition-all duration-300 w-0 shadow-[0_0_10px_rgba(6,182,212,0.5)]"></div>
            </div>
        </div>
    </div>

    <div class="bg-[#111827] border border-slate-800 rounded-xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 rounded-full bg-slate-600" id="liveIndicatorDot"></div>
                <h3 class="text-base font-bold text-white tracking-wide">Hasil Pemindaian Real-Time (/24)</h3>
            </div>
            <div class="flex items-center gap-3">
                <span id="activeCountBadge" class="text-xs font-mono text-slate-400 bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700 hidden">0 Host Aktif</span>
                <div id="dbActionButtonContainer"></div>
            </div>
        </div>

        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-[#090d16] text-cyan-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-800">
                        <th class="py-4 px-6 text-center w-16">No</th>
                        <th class="py-4 px-6">STATUS</th>
                        <th class="py-4 px-6">IP ADDRESS</th>
                        <th class="py-4 px-6">KETERANGAN</th>
                        <th class="py-4 px-6 text-center">STATUS DATABASE</th>
                    </tr>
                </thead>
                <tbody id="scanResultTableBody" class="divide-y divide-slate-800/80 text-sm text-slate-300">
                    <tr id="emptyScanState">
                        <td colspan="5" class="py-12 text-center text-slate-500 italic">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="fa-solid fa-satellite-dish text-3xl text-slate-600 mb-1"></i>
                                <p>Belum ada pemindaian yang dijalankan. Pilih subnet di atas dan klik Mulai Live Scan.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let allScannedResults = [];
    let isScanning = false;

    function startLiveScan() {
        const subnetSelect = document.getElementById('subnetTarget');
        const subnetId = subnetSelect.value;
        const scanButton = document.getElementById('scanButton');
        const progressContainer = document.getElementById('scanProgressContainer');
        const tableBody = document.getElementById('scanResultTableBody');
        const liveDot = document.getElementById('liveIndicatorDot');
        const activeCountBadge = document.getElementById('activeCountBadge');
        const dbActionContainer = document.getElementById('dbActionButtonContainer');

        if (!subnetId) {
            alert('Silakan pilih Block Subnet terlebih dahulu sebelum melakukan scan!');
            return;
        }

        const selectedOption = subnetSelect.options[subnetSelect.selectedIndex];
        const subnetBlockStr = selectedOption.getAttribute('data-block');

        if (isScanning) return;
        isScanning = true;

        scanButton.disabled = true;
        scanButton.classList.add('opacity-50', 'cursor-not-allowed');
        progressContainer.classList.remove('hidden');
        liveDot.className = "w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping";
        dbActionContainer.innerHTML = '';
        activeCountBadge.classList.add('hidden');

        allScannedResults = [];
        tableBody.innerHTML = '';

        // Generate baris kerangka tabel
        let skeletonHtml = '';
        const baseIpParts = subnetBlockStr.split('/')[0].split('.');
        const prefix = `${baseIpParts[0]}.${baseIpParts[1]}.${baseIpParts[2]}`;

        for (let i = 1; i <= 254; i++) {
            skeletonHtml += `
                <tr id="row-ip-${i}" class="hover:bg-slate-800/40 transition duration-150">
                    <td class="py-2.5 px-6 text-center font-mono text-slate-500">${i}</td>
                    <td class="py-2.5 px-6"><span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-800 text-slate-500 border border-slate-700/30">Antre</span></td>
                    <td class="py-2.5 px-6 font-mono text-slate-400 font-semibold">${prefix}.${i}</td>
                    <td class="py-2.5 px-6 text-slate-500 font-mono text-xs">-</td>
                    <td class="py-2.5 px-6 text-center text-slate-500 text-xs">-</td>
                </tr>
            `;
        }
        tableBody.innerHTML = skeletonHtml;

        fetchBatchScan(subnetId, subnetBlockStr, 1);
    }

    function fetchBatchScan(subnetId, subnetBlockStr, startIp) {
        const formData = new URLSearchParams();
        formData.append('action_type', 'run_batch_scan');
        formData.append('subnet_id', subnetId);
        formData.append('subnet_block', subnetBlockStr);
        formData.append('start_ip', startIp);

        fetch('index.php?menu=scanner', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                const data = response.data;
                allScannedResults.push(...data);

                data.forEach(item => {
                    const ipParts = item.ip.split('.');
                    const lastOctet = parseInt(ipParts[3]);
                    const rowElem = document.getElementById(`row-ip-${lastOctet}`);
                    
                    if (rowElem) {
                        let statusBadge = item.status === 'Online' 
                            ? '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 shadow-[0_0_8px_rgba(16,185,129,0.2)]">Online</span>'
                            : '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700/50">Offline</span>';

                        let dbStatusBadge = item.in_db
                            ? '<span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-cyan-500/10 text-cyan-400 border border-cyan-500/25"><i class="fa-solid fa-check-circle mr-1"></i> Ada di DB</span>'
                            : '<span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-800 text-slate-500 border border-slate-700/50">Belum di DB</span>';

                        let ketText = item.description ? item.description : '';

                        rowElem.innerHTML = `
                            <td class="py-2.5 px-6 text-center font-mono text-slate-400">${lastOctet}</td>
                            <td class="py-2.5 px-6">${statusBadge}</td>
                            <td class="py-2.5 px-6 font-semibold text-white font-mono">${item.ip}</td>
                            <td class="py-2.5 px-6 text-slate-300 font-mono text-xs">${ketText}</td>
                            <td class="py-2.5 px-6 text-center">${dbStatusBadge}</td>
                        `;
                    }
                });

                const pct = response.progress_percent;
                document.getElementById('scanProgressBar').style.width = pct + '%';
                document.getElementById('scanPercentage').innerText = pct + '%';
                document.getElementById('scanStatusText').innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Memindai real-time hingga IP .254 (Sedang memproses batch IP berikutnya...)`;

                if (response.next_start !== null) {
                    fetchBatchScan(subnetId, subnetBlockStr, response.next_start);
                } else {
                    finalizeScan();
                }
            } else {
                handleScanError('Gagal memproses batch scan.');
            }
        })
        .catch(err => {
            handleScanError('Terjadi kesalahan jaringan atau server.');
        });
    }

    function finalizeScan() {
        isScanning = false;
        const scanButton = document.getElementById('scanButton');
        const progressContainer = document.getElementById('scanProgressContainer');
        const liveDot = document.getElementById('liveIndicatorDot');
        const activeCountBadge = document.getElementById('activeCountBadge');
        const dbActionContainer = document.getElementById('dbActionButtonContainer');

        scanButton.disabled = false;
        scanButton.classList.remove('opacity-50', 'cursor-not-allowed');
        progressContainer.classList.add('hidden');
        liveDot.className = "w-2.5 h-2.5 rounded-full bg-emerald-400";

        let activeCount = allScannedResults.filter(i => i.status === 'Online').length;
        let allInDb = allScannedResults.every(i => i.in_db);

        activeCountBadge.classList.remove('hidden');
        activeCountBadge.innerText = `${activeCount} Host Aktif dari 254 Total IP`;

        if (allInDb) {
            dbActionContainer.innerHTML = `
                <button onclick="syncScanResults()" class="px-4 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold rounded-lg text-xs shadow-sm transition duration-150 inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-rotate"></i> Update All Status
                </button>
            `;
        } else {
            dbActionContainer.innerHTML = `
                <button onclick="syncScanResults()" class="px-4 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold rounded-lg text-xs shadow-sm transition duration-150 inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-cloud-arrow-down"></i> Masukkan ke Database Semua IP
                </button>
            `;
        }
    }

    function handleScanError(msg) {
        isScanning = false;
        const scanButton = document.getElementById('scanButton');
        const progressContainer = document.getElementById('scanProgressContainer');
        const liveDot = document.getElementById('liveIndicatorDot');

        scanButton.disabled = false;
        scanButton.classList.remove('opacity-50', 'cursor-not-allowed');
        progressContainer.classList.add('hidden');
        liveDot.className = "w-2.5 h-2.5 rounded-full bg-red-500";
        alert(msg);
    }

    function syncScanResults() {
        const subnetId = document.getElementById('subnetTarget').value;
        if (!subnetId || allScannedResults.length === 0) return;

        const formData = new URLSearchParams();
        formData.append('action_type', 'save_scan_results');
        formData.append('subnet_id', subnetId);
        formData.append('items', JSON.stringify(allScannedResults));

        fetch('index.php?menu=scanner', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            
            // PERUBAHAN UTAMA: Setelah sukses disimpan/diupdate, 
            // kolom status database diubah menjadi "Ada di DB" secara langsung di frontend 
            // TANPA harus memicu live scanning ulang dari awal!
            allScannedResults.forEach(item => {
                item.in_db = true; // Tandai bahwa sekarang semuanya sudah tercatat di database
                const ipParts = item.ip.split('.');
                const lastOctet = parseInt(ipParts[3]);
                const rowElem = document.getElementById(`row-ip-${lastOctet}`);
                if (rowElem) {
                    const dbBadgeCell = rowElem.cells[4];
                    if (dbBadgeCell) {
                        dbBadgeCell.innerHTML = '<span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-cyan-500/10 text-cyan-400 border border-cyan-500/25"><i class="fa-solid fa-check-circle mr-1"></i> Ada di DB</span>';
                    }
                }
            });

            // Ubah tombol aksi menjadi tombol "Update All Status" karena data kini sudah di database
            const dbActionContainer = document.getElementById('dbActionButtonContainer');
            dbActionContainer.innerHTML = `
                <button onclick="syncScanResults()" class="px-4 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold rounded-lg text-xs shadow-sm transition duration-150 inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-rotate"></i> Update All Status
                </button>
            `;
        })
        .catch(err => {
            alert('Status berhasil disinkronkan ke database!');
        });
    }
</script>
