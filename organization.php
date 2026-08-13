<?php
$action = isset($_GET['act']) ? $_GET['act'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$org_id = isset($_GET['org_id']) ? intval($_GET['org_id']) : 0;

// 1. HANDLE POST UNTUK ORGANISASI (Simpan / Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'org') {
    $org_name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $edit_id = intval($_POST['id']);
        $conn->query("UPDATE organizations SET name='$org_name', description='$description' WHERE id=$edit_id");
    } else {
        $conn->query("INSERT INTO organizations (name, description) VALUES ('$org_name', '$description')");
    }
    echo "<script>window.location='index.php?menu=organization';</script>";
}

// 2. HANDLE DELETE ORGANISASI
if ($action == 'delete' && $id > 0) {
    $conn->query("DELETE FROM organizations WHERE id=$id");
    echo "<script>window.location='index.php?menu=organization';</script>";
}

// 3. HANDLE POST UNTUK AS NUMBER (Simpan / Edit AS)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'asn') {
    $parent_org_id = intval($_POST['organization_id']);
    $as_number = $conn->real_escape_string($_POST['as_number']);
    $asn_desc = $conn->real_escape_string($_POST['description']);

    if (isset($_POST['asn_id']) && !empty($_POST['asn_id'])) {
        $asn_edit_id = intval($_POST['asn_id']);
        $conn->query("UPDATE as_numbers SET as_number='$as_number', description='$asn_desc' WHERE id=$asn_edit_id");
    } else {
        $conn->query("INSERT INTO as_numbers (organization_id, as_number, description) VALUES ($parent_org_id, '$as_number', '$asn_desc')");
    }
    echo "<script>window.location='index.php?menu=organization&act=asn&org_id=$parent_org_id';</script>";
}

// 4. HANDLE DELETE AS NUMBER
if ($action == 'delete_asn' && $id > 0) {
    $back_org = intval($_GET['org_id']);
    $conn->query("DELETE FROM as_numbers WHERE id=$id");
    echo "<script>window.location='index.php?menu=organization&act=asn&org_id=$back_org';</script>";
}
?>

<?php if ($action == 'asn'): ?>
    <?php
    $org_info_res = $conn->query("SELECT * FROM organizations WHERE id=$org_id");
    $org_info = ($org_info_res && $org_info_res->num_rows > 0) ? $org_info_res->fetch_assoc() : ['name' => 'Unknown'];
    ?>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-slate-800/90 text-cyan-400 rounded-xl border border-slate-700 shadow-sm">
                    <i class="fa-solid fa-network-wired text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white tracking-wide">Manage AS Number</h2>
                    <p class="text-slate-400 text-sm">Instansi: <span class="text-cyan-400 font-semibold"><?= htmlspecialchars($org_info['name']) ?></span></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openAsnModal('add')" class="px-4 py-2.5 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-semibold rounded-lg text-sm shadow-sm transition duration-200 inline-flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Tambah AS Number
                </button>
                <a href="index.php?menu=organization" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-lg text-sm border border-slate-700 transition duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="bg-[#111827] border border-slate-800 rounded-xl overflow-hidden shadow-xl">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-base font-bold text-white tracking-wide">Daftar AS Number Terikat</h3>
                <span class="text-xs font-mono text-cyan-400 bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700">ACTIVE LIST</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#090d16]/80 text-cyan-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-800">
                            <th class="py-4 px-6 text-center w-16">No</th>
                            <th class="py-4 px-6">AS Number</th>
                            <th class="py-4 px-6">Description</th>
                            <th class="py-4 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 text-sm text-slate-300">
                        <?php
                        $no_asn = 1;
                        $q_asn_table = $conn->query("SELECT * FROM as_numbers WHERE organization_id = $org_id ORDER BY id DESC");
                        if ($q_asn_table && $q_asn_table->num_rows > 0):
                            while ($row_asn = $q_asn_table->fetch_assoc()):
                        ?>
                        <tr class="hover:bg-slate-800/40 transition duration-150">
                            <td class="py-4 px-6 text-center font-mono text-slate-400"><?= $no_asn++ ?></td>
                            <td class="py-4 px-6 font-semibold text-white font-mono"><?= htmlspecialchars($row_asn['as_number']) ?></td>
                            <td class="py-4 px-6 text-slate-400"><?= htmlspecialchars($row_asn['description'] ?: '-') ?></td>
                            <td class="py-4 px-6 text-center">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <button onclick="openAsnModal('edit', '<?= $row_asn['id'] ?>', '<?= htmlspecialchars($row_asn['as_number'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row_asn['description'], ENT_QUOTES) ?>')" title="Edit" 
                                       class="p-1.5 bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-amber-400 rounded-lg text-xs transition duration-200 border border-slate-700">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="index.php?menu=organization&act=delete_asn&org_id=<?= $org_id ?>&id=<?= $row_asn['id'] ?>" title="Delete" 
                                       onclick="return confirm('Yakin ingin menghapus AS Number ini?')" 
                                       class="p-1.5 bg-slate-800 hover:bg-rose-500 hover:text-slate-950 text-rose-400 rounded-lg text-xs transition duration-200 border border-slate-700">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500 italic">Belum ada AS Number terdaftar untuk instansi ini.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="asnModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden">
        <div class="bg-[#111827] border border-slate-800 rounded-2xl w-full max-w-lg p-6 lg:p-8 shadow-2xl relative">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
                <h3 id="asnModalTitle" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-network-wired text-cyan-400"></i> Tambah AS Number Baru
                </h3>
                <button onclick="closeAsnModal()" class="text-slate-400 hover:text-white text-xl focus:outline-none">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="index.php?menu=organization&act=asn&org_id=<?= $org_id ?>" method="POST" class="space-y-5">
                <input type="hidden" name="form_type" value="asn">
                <input type="hidden" name="organization_id" value="<?= $org_id ?>">
                <input type="hidden" name="asn_id" id="modalAsnId" value="">

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">AS Number</label>
                    <input type="text" name="as_number" id="modalAsnNumber" required 
                           class="w-full bg-[#090d16] border border-slate-800 rounded-lg px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-cyan-500 text-sm font-mono"
                           placeholder="Contoh: AS13456 atau 65001">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Description</label>
                    <textarea name="description" id="modalAsnDesc" rows="3"
                              class="w-full bg-[#090d16] border border-slate-800 rounded-lg px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-cyan-500 text-sm"
                              placeholder="Keterangan AS Number..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" onclick="closeAsnModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-lg text-sm border border-slate-700">
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
        function openAsnModal(mode, id = '', asn = '', desc = '') {
            const modal = document.getElementById('asnModal');
            const title = document.getElementById('asnModalTitle');
            const asnId = document.getElementById('modalAsnId');
            const asnNumber = document.getElementById('modalAsnNumber');
            const asnDesc = document.getElementById('modalAsnDesc');

            if (mode === 'edit') {
                title.innerHTML = '<i class="fa-solid fa-pen-to-square text-cyan-400"></i> Edit AS Number';
                asnId.value = id;
                asnNumber.value = asn;
                asnDesc.value = desc;
            } else {
                title.innerHTML = '<i class="fa-solid fa-network-wired text-cyan-400"></i> Tambah AS Number Baru';
                asnId.value = '';
                asnNumber.value = '';
                asnDesc.value = '';
            }
            modal.classList.remove('hidden');
        }

        function closeAsnModal() {
            document.getElementById('asnModal').classList.add('hidden');
        }
    </script>

<?php else: ?>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-slate-800/90 text-cyan-400 rounded-xl border border-slate-700 shadow-sm">
                    <i class="fa-solid fa-building text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white tracking-wide">Manajemen Organisasi & Instansi</h2>
                    <p class="text-slate-400 text-sm">Kelola daftar instansi dan struktur AS Number terkait.</p>
                </div>
            </div>
            <button onclick="openOrgModal('add')" class="px-4 py-2.5 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-semibold rounded-lg text-sm shadow-sm transition duration-200 inline-flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Tambah Organisasi
            </button>
        </div>

        <div class="bg-[#111827] border border-slate-800 rounded-xl overflow-hidden shadow-xl">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-base font-bold text-white tracking-wide">Daftar Organisasi Terdaftar</h3>
                <span class="text-xs font-mono text-cyan-400 bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700">DATABASE ACTIVE</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#090d16]/80 text-cyan-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-800">
                            <th class="py-4 px-6 text-center w-16">No</th>
                            <th class="py-4 px-6">Organization Name</th>
                            <th class="py-4 px-6">Total AS Number</th>
                            <th class="py-4 px-6">Description</th>
                            <th class="py-4 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 text-sm text-slate-300">
                        <?php
                        $no = 1;
                        $query = $conn->query("SELECT o.*, (SELECT COUNT(*) FROM as_numbers a WHERE a.organization_id = o.id) as total_asn FROM organizations o ORDER BY o.id DESC");
                        
                        if ($query && $query->num_rows > 0):
                            while ($row = $query->fetch_assoc()):
                                $asn_list_q = $conn->query("SELECT as_number FROM as_numbers WHERE organization_id = " . $row['id']);
                                $asn_spill = [];
                                while ($asn_row = $asn_list_q->fetch_assoc()) {
                                    $asn_spill[] = 'AS'.$asn_row['as_number'];
                                }
                        ?>
                        <tr class="hover:bg-slate-800/40 transition duration-150">
                            <td class="py-4 px-6 text-center font-mono text-slate-400"><?= $no++ ?></td>
                            
                            <td class="py-4 px-6 font-semibold text-white">
                                <?= htmlspecialchars($row['name']) ?>
                            </td>

                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-800 text-cyan-400 border border-slate-700">
                                    <?= $row['total_asn'] ?> AS Number
                                </span>
                                <?php if (!empty($asn_spill)): ?>
                                    <div class="text-xs text-slate-400 mt-1 font-mono">
                                        [<?= implode(', ', $asn_spill) ?>]
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="py-4 px-6 text-slate-400">
                                <?= htmlspecialchars($row['description'] ?: '-') ?>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a href="index.php?menu=organization&act=asn&org_id=<?= $row['id'] ?>" title="Manage AS Number" 
                                       class="px-2.5 py-1.5 bg-slate-800 hover:bg-cyan-500 hover:text-slate-950 text-cyan-400 rounded-lg text-xs font-medium transition duration-200 border border-slate-700 inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-network-wired"></i> Manage AS
                                    </a>
                                    <button onclick="openOrgModal('edit', '<?= $row['id'] ?>', '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>')" title="Edit" 
                                       class="p-1.5 bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-amber-400 rounded-lg text-xs transition duration-200 border border-slate-700">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="index.php?menu=organization&act=delete&id=<?= $row['id'] ?>" title="Delete" 
                                       onclick="return confirm('Yakin ingin menghapus instansi ini?')" 
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
                            <td colspan="5" class="py-8 text-center text-slate-500 italic">Belum ada data organisasi yang tersimpan di database.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="orgModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden">
        <div class="bg-[#111827] border border-slate-800 rounded-2xl w-full max-w-lg p-6 lg:p-8 shadow-2xl relative">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
                <h3 id="orgModalTitle" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-building text-cyan-400"></i> Tambah Organisasi Baru
                </h3>
                <button onclick="closeOrgModal()" class="text-slate-400 hover:text-white text-xl focus:outline-none">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="index.php?menu=organization" method="POST" class="space-y-5">
                <input type="hidden" name="form_type" value="org">
                <input type="hidden" name="id" id="modalOrgId" value="">

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Organization Name</label>
                    <input type="text" name="name" id="modalOrgName" required 
                           class="w-full bg-[#090d16] border border-slate-800 rounded-lg px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-cyan-500 text-sm"
                           placeholder="Contoh: PT Cyber Nusantara Utama">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Description</label>
                    <textarea name="description" id="modalOrgDesc" rows="3"
                              class="w-full bg-[#090d16] border border-slate-800 rounded-lg px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-cyan-500 text-sm"
                              placeholder="Keterangan singkat instansi..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" onclick="closeOrgModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-lg text-sm border border-slate-700">
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
        function openOrgModal(mode, id = '', name = '', desc = '') {
            const modal = document.getElementById('orgModal');
            const title = document.getElementById('orgModalTitle');
            const orgId = document.getElementById('modalOrgId');
            const orgName = document.getElementById('modalOrgName');
            const orgDesc = document.getElementById('modalOrgDesc');

            if (mode === 'edit') {
                title.innerHTML = '<i class="fa-solid fa-pen-to-square text-cyan-400"></i> Edit Organisasi';
                orgId.value = id;
                orgName.value = name;
                orgDesc.value = desc;
            } else {
                title.innerHTML = '<i class="fa-solid fa-building text-cyan-400"></i> Tambah Organisasi Baru';
                orgId.value = '';
                orgName.value = '';
                orgDesc.value = '';
            }
            modal.classList.remove('hidden');
        }

        function closeOrgModal() {
            document.getElementById('orgModal').classList.add('hidden');
        }
    </script>
<?php endif; ?>
