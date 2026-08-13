<?php
// Endpoint AJAX untuk Live Edit Deskripsi IP (Diproses otomatis di file yang sama)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_type']) && $_POST['action_type'] == 'live_edit_desc') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';

    if ($id > 0) {
        $query = $conn->query("UPDATE ip_addresses SET description = '$description' WHERE id = $id");
        if ($query) {
            echo json_encode(['status' => 'success', 'message' => 'Deskripsi berhasil diperbarui']);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui deskripsi']);
    exit;
}

$action = isset($_GET['act']) ? $_GET['act'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$subnet_id = isset($_GET['subnet_id']) ? intval($_GET['subnet_id']) : 0;

// 1. HANDLE POST UNTUK BLOCK SUBNET (Simpan / Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'subnet') {
    $organization_id = intval($_POST['organization_id']);
    $as_number_id = intval($_POST['as_number_id']);
    $subnet_block = $conn->real_escape_string($_POST['subnet_block']);
    $description = $conn->real_escape_string($_POST['description']);

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $edit_id = intval($_POST['id']);
        $conn->query("UPDATE block_subnets SET organization_id=$organization_id, as_number_id=$as_number_id, subnet_block='$subnet_block', description='$description' WHERE id=$edit_id");
    } else {
        $conn->query("INSERT INTO block_subnets (organization_id, as_number_id, subnet_block, description) VALUES ($organization_id, $as_number_id, '$subnet_block', '$description')");
    }
    echo "<script>window.location='index.php?menu=subnet';</script>";
}

// 2. HANDLE DELETE BLOCK SUBNET
if ($action == 'delete' && $id > 0) {
    $conn->query("DELETE FROM block_subnets WHERE id=$id");
    echo "<script>window.location='index.php?menu=subnet';</script>";
}

// 3. HANDLE POST UNTUK MANAGE IP ADDRESS (Tambah IP / Edit Status Saja)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'ip') {
    $s_id = intval($_POST['subnet_id']);
    $ip_address = $conn->real_escape_string($_POST['ip_address']);
    $status = $conn->real_escape_string($_POST['status']);

    if (isset($_POST['ip_id']) && !empty($_POST['ip_id'])) {
        $ip_edit_id = intval($_POST['ip_id']);
        $conn->query("UPDATE ip_addresses SET ip_address='$ip_address', status='$status' WHERE id=$ip_edit_id");
    } else {
        $conn->query("INSERT INTO ip_addresses (subnet_id, ip_address, status, description) VALUES ($s_id, '$ip_address', '$status', '')");
    }
    echo "<script>window.location='index.php?menu=subnet&act=manage_ip&subnet_id=$s_id';</script>";
}

// 4. HANDLE TOGGLE STATUS IP (Activate / Non-aktif)
if ($action == 'toggle_status' && $id > 0) {
    $back_sub = intval($_GET['subnet_id']);
    $curr_status = $_GET['current'];
    $new_status = (strtolower($curr_status) == 'active') ? 'Offline' : 'Active';
    
    $conn->query("UPDATE ip_addresses SET status='$new_status' WHERE id=$id");
    echo "<script>window.location='index.php?menu=subnet&act=manage_ip&subnet_id=$back_sub';</script>";
}

// 5. HANDLE DELETE IP ADDRESS
if ($action == 'delete_ip' && $id > 0) {
    $back_sub = intval($_GET['subnet_id']);
    $conn->query("DELETE FROM ip_addresses WHERE id=$id");
    echo "<script>window.location='index.php?menu=subnet&act=manage_ip&subnet_id=$back_sub';</script>";
}
?>

<?php if ($action == 'manage_ip'): ?>
    <?php
    $sub_info_res = $conn->query("SELECT b.*, o.name as org_name, a.as_number FROM block_subnets b LEFT JOIN organizations o ON b.organization_id = o.id LEFT JOIN as_numbers a ON b.as_number_id = a.id WHERE b.id = $subnet_id");
    $sub_info = ($sub_info_res && $sub_info_res->num_rows > 0) ? $sub_info_res->fetch_assoc() : ['subnet_block' => 'Unknown', 'org_name' => '-', 'as_number' => '-'];
    ?>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-slate-800/90 text-cyan-400 rounded-xl border border-slate-700 shadow-sm">
                    <i class="fa-solid fa-laptop-code text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white tracking-wide">Manage IP Address</h2>
                    <p class="text-slate-400 text-sm">Subnet: <span class="text-cyan-400 font-semibold font-mono"><?= htmlspecialchars($sub_info['subnet_block']) ?></span> | Instansi: <span class="text-slate-200"><?= htmlspecialchars($sub_info['org_name']) ?></span> (<?= htmlspecialchars($sub_info['as_number']) ?>)</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openIpModal('add')" class="px-4 py-2.5 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-semibold rounded-lg text-sm shadow-sm transition duration-200 inline-flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Tambah IP Address
                </button>
                <a href="index.php?menu=subnet" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-lg text-sm border border-slate-700 transition duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="bg-[#111827] border border-slate-800 rounded-xl overflow-hidden shadow-xl">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white tracking-wide">Daftar Alokasi IP Address</h3>
                </div>
                <span class="text-xs font-mono text-cyan-400 bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700">SUBNET MAPPING</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#090d16]/80 text-cyan-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-800">
                            <th class="py-4 px-6 text-center w-16">No</th>
                            <th class="py-4 px-6">IP Address</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6">Description</th>
                            <th class="py-4 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 text-sm text-slate-300">
                        <?php
                        $no_ip = 1;
                        $q_ip_table = $conn->query("SELECT * FROM ip_addresses WHERE subnet_id = $subnet_id ORDER BY id ASC");
                        if ($q_ip_table && $q_ip_table->num_rows > 0):
                            while ($row_ip = $q_ip_table->fetch_assoc()):
                                $is_active = (strtolower($row_ip['status']) == 'active' || strtolower($row_ip['status']) == 'online');
                        ?>
                        <tr class="hover:bg-slate-800/40 transition duration-150">
                            <td class="py-4 px-6 text-center font-mono text-slate-400"><?= $no_ip++ ?></td>
                            <td class="py-4 px-6 font-semibold text-white font-mono"><?= htmlspecialchars($row_ip['ip_address']) ?></td>
                            <td class="py-4 px-6">
                                <?php if($is_active): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-700/50 text-slate-400 border border-slate-600/30"><?= htmlspecialchars($row_ip['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6">
                                <div class="relative group">
                                    <input type="text" 
                                           value="<?= htmlspecialchars($row_ip['description']) ?>" 
                                           placeholder="Klik untuk isi keterangan..."
                                           onblur="updateDescription(<?= $row_ip['id'] ?>, this.value)"
                                           onkeypress="if(event.key === 'Enter'){ this.blur(); }"
                                           class="w-full bg-transparent border border-transparent hover:border-slate-700 focus:border-cyan-500 focus:bg-[#090d16] rounded-lg px-3 py-1.5 text-slate-200 placeholder-slate-600 focus:outline-none transition duration-150 text-sm">
                                    <span id="status-badge-<?= $row_ip['id'] ?>" class="absolute right-2 top-2 text-[10px] text-cyan-400 opacity-0 transition-opacity duration-200 pointer-events-none font-mono">Saved</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a href="index.php?menu=subnet&act=toggle_status&subnet_id=<?= $subnet_id ?>&id=<?= $row_ip['id'] ?>&current=<?= urlencode($row_ip['status']) ?>" 
                                       title="<?= $is_active ? 'Klik untuk menonaktifkan' : 'Klik untuk mengaktifkan' ?>"
                                       class="px-3 py-1.5 rounded-lg text-xs font-medium transition duration-200 border inline-flex items-center gap-1.5 <?= $is_active ? 'bg-slate-800 hover:bg-slate-700 text-slate-400 border-slate-700' : 'bg-emerald-500 hover:bg-emerald-600 text-slate-950 border-emerald-600 shadow-sm'?>">
                                        <i class="fa-solid <?= $is_active ? 'fa-power-off text-slate-400' : 'fa-check text-slate-950' ?>"></i> 
                                        Turn
                                    </a>
                                    <button onclick="openIpModal('edit', '<?= $row_ip['id'] ?>', '<?= htmlspecialchars($row_ip['ip_address'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row_ip['status'], ENT_QUOTES) ?>')" title="Edit Alamat IP" 
                                       class="p-1.5 bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-amber-400 rounded-lg text-xs transition duration-200 border border-slate-700">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="index.php?menu=subnet&act=delete_ip&subnet_id=<?= $subnet_id ?>&id=<?= $row_ip['id'] ?>" title="Delete" 
                                       onclick="return confirm('Yakin ingin menghapus IP Address ini?')" 
                                       class="p-1.5 bg-slate-800 hover:bg-rose-500 hover:text-slate-950 text-rose-400 rounded-lg text-xs transition duration-200 border border-slate-700">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 italic">Belum ada IP Address terdaftar di subnet ini.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="ipModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden">
        <div class="bg-[#111827] border border-slate-800 rounded-2xl w-full max-w-lg p-6 lg:p-8 shadow-2xl relative">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
                <h3 id="ipModalTitle" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-laptop-code text-cyan-400"></i> Tambah IP Address Baru
                </h3>
                <button onclick="closeIpModal()" class="text-slate-400 hover:text-white text-xl focus:outline-none">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="index.php?menu=subnet&act=manage_ip&subnet_id=<?= $subnet_id ?>" method="POST" class="space-y-4">
                <input type="hidden" name="form_type" value="ip">
                <input type="hidden" name="subnet_id" value="<?= $subnet_id ?>">
                <input type="hidden" name="ip_id" id="modalIpId" value="">

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">IP Address</label>
                    <input type="text" name="ip_address" id="modalIpAddress" required 
                           class="w-full bg-[#090d16] border border-slate-800 rounded-lg px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-cyan-500 text-sm font-mono"
                           placeholder="Contoh: 192.168.1.10">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                    <select name="status" id="modalIpStatus" class="w-full bg-[#090d16] border border-slate-800 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 text-sm">
                        <option value="Active">Active</option>
                        <option value="Offline">Offline</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" onclick="closeIpModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-lg text-sm border border-slate-700">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-semibold rounded-lg text-sm shadow-sm inline-flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateDescription(id, value) {
            const badge = document.getElementById('status-badge-' + id);
            
            fetch('index.php?menu=subnet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action_type=live_edit_desc&id=' + encodeURIComponent(id) + '&description=' + encodeURIComponent(value)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    badge.classList.remove('opacity-0');
                    setTimeout(() => {
                        badge.classList.add('opacity-0');
                    }, 1500);
                } else {
                    alert('Gagal menyimpan deskripsi');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function openIpModal(mode, id = '', ip = '', status = 'Active') {
            const modal = document.getElementById('ipModal');
            const title = document.getElementById('ipModalTitle');
            const ipId = document.getElementById('modalIpId');
            const ipAddress = document.getElementById('modalIpAddress');
            const ipStatus = document.getElementById('modalIpStatus');

            if (mode === 'edit') {
                title.innerHTML = '<i class="fa-solid fa-pen-to-square text-cyan-400"></i> Edit IP Address Details';
                ipId.value = id;
                ipAddress.value = ip;
                ipStatus.value = status;
            } else {
                title.innerHTML = '<i class="fa-solid fa-laptop-code text-cyan-400"></i> Tambah IP Address Baru';
                ipId.value = '';
                ipAddress.value = '';
                ipStatus.value = 'Active';
            }
            modal.classList.remove('hidden');
        }

        function closeIpModal() {
            document.getElementById('ipModal').classList.add('hidden');
        }
    </script>

<?php else: ?>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-slate-800/90 text-cyan-400 rounded-xl border border-slate-700 shadow-sm">
                    <i class="fa-solid fa-network-wired text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white tracking-wide">Manajemen Block Subnet</h2>
                    <p class="text-slate-400 text-sm">Kelola blok subnet jaringan, alokasi instansi, serta relasi AS Number.</p>
                </div>
            </div>
            <button onclick="openSubnetModal('add')" class="px-4 py-2.5 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-semibold rounded-lg text-sm shadow-sm transition duration-200 inline-flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Tambah Block Subnet
            </button>
        </div>

        <div class="bg-[#111827] border border-slate-800 rounded-xl overflow-hidden shadow-xl">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-base font-bold text-white tracking-wide">Daftar Block Subnet Terdaftar</h3>
                <span class="text-xs font-mono text-cyan-400 bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700">SUBNET ACTIVE</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#090d16]/80 text-cyan-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-800">
                            <th class="py-4 px-6 text-center w-16">No</th>
                            <th class="py-4 px-6">Block Subnet</th>
                            <th class="py-4 px-6">Organization</th>
                            <th class="py-4 px-6">AS Number</th>
                            <th class="py-4 px-6 text-center">Total IP</th>
                            <th class="py-4 px-6">Description</th>
                            <th class="py-4 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 text-sm text-slate-300">
                        <?php
                        $no = 1;
                        $query = $conn->query("SELECT b.*, o.name as org_name, a.as_number, (SELECT COUNT(*) FROM ip_addresses i WHERE i.subnet_id = b.id) as total_ip FROM block_subnets b LEFT JOIN organizations o ON b.organization_id = o.id LEFT JOIN as_numbers a ON b.as_number_id = a.id ORDER BY b.id DESC");
                        
                        if ($query && $query->num_rows > 0):
                            while ($row = $query->fetch_assoc()):
                        ?>
                        <tr class="hover:bg-slate-800/40 transition duration-150">
                            <td class="py-4 px-6 text-center font-mono text-slate-400"><?= $no++ ?></td>
                            
                            <td class="py-4 px-6 font-semibold text-white font-mono">
                                <?= htmlspecialchars($row['subnet_block']) ?>
                            </td>

                            <td class="py-4 px-6 text-slate-200">
                                <?= htmlspecialchars($row['org_name'] ?: '-') ?>
                            </td>

                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-medium bg-slate-800 text-cyan-400 border border-slate-700">
                                    AS<?= htmlspecialchars($row['as_number'] ?: '-') ?>
                                </span>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                    <?= $row['total_ip'] ?> IP
                                </span>
                            </td>

                            <td class="py-4 px-6 text-slate-400">
                                <?= htmlspecialchars($row['description'] ?: '-') ?>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a href="index.php?menu=subnet&act=manage_ip&subnet_id=<?= $row['id'] ?>" title="Manage IP Address" 
                                       class="px-2.5 py-1.5 bg-slate-800 hover:bg-cyan-500 hover:text-slate-950 text-cyan-400 rounded-lg text-xs font-medium transition duration-200 border border-slate-700 inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-laptop-code"></i> Manage IP
                                    </a>
                                    <button onclick="openSubnetModal('edit', '<?= $row['id'] ?>', '<?= $row['organization_id'] ?>', '<?= $row['as_number_id'] ?>', '<?= htmlspecialchars($row['subnet_block'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>')" title="Edit" 
                                       class="p-1.5 bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-amber-400 rounded-lg text-xs transition duration-200 border border-slate-700">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="index.php?menu=subnet&act=delete&id=<?= $row['id'] ?>" title="Delete" 
                                       onclick="return confirm('Yakin ingin menghapus Block Subnet ini?')" 
                                       class="p-1.5 bg-slate-800 hover:bg-rose-500 hover:text-slate-950 text-rose-400 rounded-lg text-xs transition duration-200 border border-slate-700">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 italic">Belum ada data Block Subnet yang tersimpan.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="subnetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden">
        <div class="bg-[#111827] border border-slate-800 rounded-2xl w-full max-w-lg p-6 lg:p-8 shadow-2xl relative">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
                <h3 id="subnetModalTitle" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-network-wired text-cyan-400"></i> Tambah Block Subnet Baru
                </h3>
                <button onclick="closeSubnetModal()" class="text-slate-400 hover:text-white text-xl focus:outline-none">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="index.php?menu=subnet" method="POST" class="space-y-4">
                <input type="hidden" name="form_type" value="subnet">
                <input type="hidden" name="id" id="modalSubnetId" value="">

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">1. Pilih Organization (Wajib)</label>
                    <select name="organization_id" id="modalOrgSelect" onchange="fetchAsnOptions(this.value)" required 
                            class="w-full bg-[#090d16] border border-slate-800 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 text-sm">
                        <option value="">-- Pilih Instansi / Organisasi --</option>
                        <?php
                        $org_q = $conn->query("SELECT id, name FROM organizations ORDER BY name ASC");
                        while ($org = $org_q->fetch_assoc()) {
                            echo "<option value='{$org['id']}'>".htmlspecialchars($org['name'])."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">2. Pilih AS Number (Berdasarkan Organisasi)</label>
                    <select name="as_number_id" id="modalAsnSelect" required 
                            class="w-full bg-[#090d16] border border-slate-800 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 text-sm">
                        <option value="">-- Pilih Organization Terlebih Dahulu --</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">3. Block Subnet</label>
                    <input type="text" name="subnet_block" id="modalSubnetBlock" required 
                           class="w-full bg-[#090d16] border border-slate-800 rounded-lg px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-cyan-500 text-sm font-mono"
                           placeholder="Contoh: 192.168.1.0/24">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Description</label>
                    <textarea name="description" id="modalSubnetDesc" rows="3"
                              class="w-full bg-[#090d16] border border-slate-800 rounded-lg px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-cyan-500 text-sm"
                              placeholder="Keterangan blok subnet..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" onclick="closeSubnetModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-lg text-sm border border-slate-700">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-semibold rounded-lg text-sm shadow-sm inline-flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function fetchAsnOptions(orgId, selectedAsnId = '') {
            const asnSelect = document.getElementById('modalAsnSelect');
            asnSelect.innerHTML = '<option value="">Memuat AS Number...</option>';

            if (!orgId) {
                asnSelect.innerHTML = '<option value="">-- Pilih Organization Terlebih Dahulu --</option>';
                return;
            }

            fetch('get_asn.php?org_id=' + orgId)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    asnSelect.innerHTML = '<option value="">-- Pilih AS Number --</option>';
                    if (!data || data.length === 0) {
                        asnSelect.innerHTML = '<option value="">Tidak ada AS Number pada instansi ini</option>';
                    } else {
                        data.forEach(item => {
                            let selected = (item.id == selectedAsnId) ? 'selected' : '';
                            let descText = item.description ? ` (${item.description})` : '';
                            asnSelect.innerHTML += `<option value="${item.id}" ${selected}>${item.as_number}${descText}</option>`;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching AS Number:', error);
                    asnSelect.innerHTML = '<option value="">Gagal memuat AS Number</option>';
                });
        }

        function openSubnetModal(mode, id = '', orgId = '', asnId = '', subnet = '', desc = '') {
            const modal = document.getElementById('subnetModal');
            const title = document.getElementById('subnetModalTitle');
            const subId = document.getElementById('modalSubnetId');
            const orgSelect = document.getElementById('modalOrgSelect');
            const subnetBlock = document.getElementById('modalSubnetBlock');
            const subnetDesc = document.getElementById('modalSubnetDesc');

            if (mode === 'edit') {
                title.innerHTML = '<i class="fa-solid fa-pen-to-square text-cyan-400"></i> Edit Block Subnet';
                subId.value = id;
                orgSelect.value = orgId;
                subnetBlock.value = subnet;
                subnetDesc.value = desc;
                fetchAsnOptions(orgId, asnId);
            } else {
                title.innerHTML = '<i class="fa-solid fa-network-wired text-cyan-400"></i> Tambah Block Subnet Baru';
                subId.value = '';
                orgSelect.value = '';
                document.getElementById('modalAsnSelect').innerHTML = '<option value="">-- Pilih Organization Terlebih Dahulu --</option>';
                subnetBlock.value = '';
                subnetDesc.value = '';
            }
            modal.classList.remove('hidden');
        }

        function closeSubnetModal() {
            document.getElementById('subnetModal').classList.add('hidden');
        }
    </script>
<?php endif; ?>
