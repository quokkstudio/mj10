<!-- //@formatter:off -->
<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
</div>
<style>
    .biz-extra-wrap{display:flex; gap:20px; align-items:flex-start;}
    .biz-extra-left{flex:1; width:50%;}
    .biz-extra-right{flex:1; width:50%;}
    .biz-photo-box{border:1px solid #e2e2e2; border-radius:4px; padding:10px; overflow:hidden;}
    .biz-photo-header{font-weight:700; margin-bottom:8px;}
    .biz-photo-main{display:flex; align-items:center; justify-content:center; gap:8px; height:300px;}
    .biz-photo-main img{max-width:100%; max-height:100%; border:1px solid #e2e2e2; object-fit:contain;}
    .biz-photo-nav{width:28px; height:28px; line-height:26px; border-radius:14px; border:1px solid #d0d0d0; background:#f7f7f7; cursor:pointer;}
    .biz-photo-list{margin:8px 0 0; padding:0; list-style:none; max-height:160px; overflow:auto;}
    .biz-photo-list li{padding:2px 0;}
    .biz-photo-list li.is-active a{font-weight:700; color:#000;}
    .biz-cert-modal{position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; display:none;}
    .biz-cert-modal__panel{max-width:860px; width:860px; height:640px; margin:60px auto; background:#fff; padding:16px; border-radius:6px; position:relative; display:flex; flex-direction:column;}
    .biz-cert-modal__viewer{flex:1; display:flex; align-items:center; justify-content:center; overflow:hidden;}
    .biz-cert-modal__viewer iframe{width:100%; height:100%;}
    .biz-cert-modal__viewer img{max-width:100%; max-height:100%; object-fit:contain;}
</style>

<div class="table-title gd-help-manual">회원 기본정보</div>
<div class="input_wrap">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th>회원번호</th>
            <td><?= gd_isset($member['memNo']); ?></td>
            <th>아이디</th>
            <td><?= gd_htmlspecialchars($member['memId']); ?></td>
        </tr>
        <tr>
            <th>이름</th>
            <td><?= gd_htmlspecialchars($member['memNm']); ?></td>
            <th>이메일</th>
            <td><?= gd_htmlspecialchars($member['email']); ?></td>
        </tr>
        <tr>
            <th>가입일</th>
            <td><?= gd_isset($member['entryDt']); ?></td>
            <th>승인여부</th>
            <td><?= ($member['appFl'] === 'y') ? '승인' : '미승인'; ?></td>
        </tr>
        <tr>
            <th>주소</th>
            <td colspan="3">
                (<?= gd_isset($member['zonecode']); ?>) <?= gd_htmlspecialchars($member['address']); ?> <?= gd_htmlspecialchars($member['addressSub']); ?>
            </td>
        </tr>
    </table>
</div>

<div class="table-title gd-help-manual">사업자 추가정보</div>
<div class="input_wrap biz-extra-wrap">
    <div class="biz-extra-left">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>업체명</th>
                <td><?= gd_htmlspecialchars(gd_isset($profile['companyName'], $member['company'])); ?></td>
            </tr>
            <tr>
                <th>사업자 등록증</th>
                <td>
                    <?php if (!empty($certification)) { ?>
                        <?php
                        $certFileName = gd_isset($certification['imageFileNm']);
                        $certImageUrl = gd_isset($certification['imageFilePath']);
                        if ($certImageUrl !== '' && strpos($certImageUrl, 'http') !== 0 && strpos($certImageUrl, '/data/') === false) {
                            $certImageUrl = '/data/' . ltrim($certImageUrl, '/');
                        }
                        $certDownloadUrl = '../member/company_certification.php?mode=download&sno=' . $certification['sno'];
                        $certExt = strtolower(pathinfo($certFileName, PATHINFO_EXTENSION));
                        ?>
                        <a href="#" class="js-certification-open" data-url="<?= $certImageUrl; ?>" data-download="<?= $certDownloadUrl; ?>" data-ext="<?= $certExt; ?>">
                            <?= gd_htmlspecialchars($certFileName); ?>
                        </a>
                    <?php } else { ?>
                        <span class="notice-info">등록된 사업자등록증이 없습니다.</span>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th>발주담당자</th>
                <td><?= gd_htmlspecialchars(gd_isset($profile['orderManagerName'])); ?> / <?= gd_htmlspecialchars(gd_isset($profile['orderManagerPhone'])); ?></td>
            </tr>
            <tr>
                <th>결제담당자</th>
                <td><?= gd_htmlspecialchars(gd_isset($profile['payManagerName'])); ?> / <?= gd_htmlspecialchars(gd_isset($profile['payManagerPhone'])); ?></td>
            </tr>
            <tr>
                <th>출입안내</th>
                <td><?= nl2br(gd_htmlspecialchars(gd_isset($profile['entryGuide']))); ?></td>
            </tr>
            <tr>
                <th>출입키 유무</th>
                <td><?= (gd_isset($profile['entryKeyYn']) === 'y') ? '있음' : '없음'; ?></td>
            </tr>
            <tr>
                <th>적재장소안내</th>
                <td><?= nl2br(gd_htmlspecialchars(gd_isset($profile['loadingPlaceText']))); ?></td>
            </tr>
        </table>
    </div>
    <div class="biz-extra-right">
        <div class="biz-photo-box">
            <div class="biz-photo-header">적재장소 사진</div>
            <?php if (!empty($photos)) { ?>
                <?php
                $photoItems = [];
                foreach ($photos as $idx => $photo) {
                    $viewUrl = gd_isset($photo['previewUrl']);
                    if ($viewUrl === '') {
                        $viewUrl = '../member/member_biz_photo.php?mode=view&sno=' . $photo['sno'];
                    }
                    $downloadUrl = '../member/member_biz_photo.php?mode=download&sno=' . $photo['sno'];
                    $photoItems[] = [
                        'viewUrl' => $viewUrl,
                        'downloadUrl' => $downloadUrl,
                        'name' => gd_isset($photo['originName']),
                    ];
                }
                $firstPhoto = $photoItems[0];
                ?>
                <div class="biz-photo-main">
                    <button type="button" class="biz-photo-nav" id="bizPhotoPrev">‹</button>
                    <img src="<?= $firstPhoto['viewUrl']; ?>" alt="" id="bizPhotoPreview"/>
                    <button type="button" class="biz-photo-nav" id="bizPhotoNext">›</button>
                </div>
                <ul class="biz-photo-list" id="bizPhotoList">
                    <?php foreach ($photoItems as $idx => $item) { ?>
                        <li data-idx="<?= $idx; ?>" data-src="<?= $item['viewUrl']; ?>">
                            <a href="<?= $item['downloadUrl']; ?>"><?= gd_htmlspecialchars($item['name']); ?></a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <div class="notice-info">등록된 적재장소 사진이 없습니다.</div>
            <?php } ?>
        </div>
    </div>
</div>

<?php if (!empty($photos)) { ?>
<script type="text/javascript">
    (function () {
        var items = <?= json_encode($photoItems ?? [], JSON_UNESCAPED_UNICODE); ?>;
        if (!items || !items.length) {
            return;
        }
        var idx = 0;
        var $img = $('#bizPhotoPreview');
        var $list = $('#bizPhotoList');
        function render(i) {
            idx = (i + items.length) % items.length;
            $img.attr('src', items[idx].viewUrl);
            $list.find('li').removeClass('is-active').eq(idx).addClass('is-active');
        }
        $('#bizPhotoPrev').on('click', function () { render(idx - 1); });
        $('#bizPhotoNext').on('click', function () { render(idx + 1); });
        $list.on('click', 'li', function () {
            render($(this).data('idx'));
        });
        render(0);
    })();
</script>
<?php } ?>

<div class="table-title gd-help-manual">
    승인/반려 처리
    <div class="pull-right">
        <button type="button" class="btn btn-sm btn-black" id="btnApprove">승인</button>
        <button type="button" class="btn btn-sm btn-white" id="btnReject">반려</button>
    </div>
</div>
<div class="input_wrap">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <?php
        $status = gd_isset($profile['approvalStatus'], 'pending');
        $statusLabel = ($status === 'approved') ? '승인' : (($status === 'rejected') ? '반려' : '승인대기');
        $treatAt = $profile['approvedAt'] ? $profile['approvedAt'] : $profile['rejectedAt'];
        $treatBy = $profile['approvedBy'] ? $profile['approvedBy'] : $profile['rejectedBy'];
        ?>
        <tr>
            <th>현재 상태</th>
            <td><?= $statusLabel; ?></td>
            <th>처리일시</th>
            <td><?= gd_isset($treatAt); ?></td>
        </tr>
        <tr>
            <th>처리자</th>
            <td><?= gd_htmlspecialchars(gd_isset($treatBy)); ?></td>
            <th>반려사유</th>
            <td><?= gd_htmlspecialchars(gd_isset($profile['rejectReason'])); ?></td>
        </tr>
        <tr>
            <th>반려사유 입력</th>
            <td colspan="3">
                <textarea name="rejectReason" id="rejectReason" class="form-control" rows="3"><?= gd_htmlspecialchars(gd_isset($profile['rejectReason'])); ?></textarea>
            </td>
        </tr>
    </table>
</div>

<div class="table-title gd-help-manual">관리자 메모</div>
<div class="input_wrap">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th>관리자메모</th>
            <td>
                <textarea name="adminMemo" id="adminMemo" class="form-control" rows="4"><?= gd_htmlspecialchars(gd_isset($profile['adminMemo'])); ?></textarea>
            </td>
        </tr>
    </table>

    <div class="table-btn">
        <button type="button" class="btn btn-lg btn-gray" id="btnSaveMemo">메모 저장</button>
        <a href="./member_biz_approval_list.php" class="btn btn-lg btn-white">목록</a>
    </div>
</div>

<form id="frmAction" action="../member/member_biz_approval_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value=""/>
    <input type="hidden" name="rejectReason" value=""/>
    <input type="hidden" name="adminMemo" value=""/>
    <?php $memNoValue = gd_isset($member['memNo'], \Request::get()->get('memNo')); ?>
    <input type="hidden" name="returnUrl" value="<?= urlencode('member_biz_approval_view.php?memNo=' . $memNoValue); ?>"/>
    <input type="hidden" name="memNo[]" value="<?= $memNoValue; ?>"/>
</form>

<iframe id="downloadFrame" style="display:none;"></iframe>

<script type="text/javascript">
    $(document).on('click', '.js-certification-download', function (e) {
        e.preventDefault();
        $('#downloadFrame').attr('src', $(this).data('url'));
    });

    $('#btnApprove').click(function () {
        dialog_confirm('선택 회원을 승인 처리하시겠습니까?', function (result) {
            if (result) {
                submitAction('approve');
            }
        });
    });

    $('#btnReject').click(function () {
        var reason = $('#rejectReason').val();
        if (!reason) {
            dialog_alert('반려 사유를 입력해주세요.');
            return;
        }
        dialog_confirm('반려 처리하시겠습니까?', function (result) {
            if (result) {
                $('#rejectReason').val(reason);
                var $ifrm = $('#ifrmProcess');
                if ($ifrm.length) {
                    $ifrm.one('load', function () {
                        location.reload();
                    });
                } else {
                    setTimeout(function () {
                        location.reload();
                    }, 600);
                }
                submitAction('reject', reason);
                dialog_alert('반려처리되었습니다.');
            }
        });
    });

    $('#btnSaveMemo').click(function () {
        dialog_confirm('메모를 저장하시겠습니까?', function (result) {
            if (result) {
                submitAction('save_memo');
            }
        });
    });

    function submitAction(mode, reason) {
        var $form = $('#frmAction');
        if (mode === 'reject' && (!reason || reason === '')) {
            reason = $('#rejectReason').val();
        }
        $form.find('input[name="mode"]').val(mode);
        $form.find('input[name="rejectReason"]').val(reason || '');
        $form.find('input[name="adminMemo"]').val($('#adminMemo').val());
        $form.submit();
    }

    (function () {
        var items = <?= json_encode($photoItems ?? [], JSON_UNESCAPED_UNICODE); ?>;
        if (!items || !items.length) {
            return;
        }
        var idx = 0;
        var $img = $('#bizPhotoPreview');
        var $list = $('#bizPhotoList');
        function render(i) {
            idx = (i + items.length) % items.length;
            $img.attr('src', items[idx].viewUrl);
            $list.find('li').removeClass('is-active').eq(idx).addClass('is-active');
        }
        $('#bizPhotoPrev').on('click', function () { render(idx - 1); });
        $('#bizPhotoNext').on('click', function () { render(idx + 1); });
        $list.on('click', 'li', function () {
            render($(this).data('idx'));
        });
        var startX = 0;
        $img.on('touchstart', function (e) {
            startX = e.originalEvent.touches[0].clientX;
        });
        $img.on('touchend', function (e) {
            var endX = e.originalEvent.changedTouches[0].clientX;
            var diff = endX - startX;
            if (Math.abs(diff) > 30) {
                render(diff < 0 ? idx + 1 : idx - 1);
            }
        });
        render(0);
    })();

    $(document).on('click', '.js-certification-open', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        var ext = $(this).data('ext');
        var downloadUrl = $(this).data('download');
        if (!url) {
            dialog_alert('미리보기를 사용할 수 없습니다.');
            return;
        }
        $('#bizCertModal').removeClass('dn').show();
        if (ext === 'pdf') {
            $('#bizCertModalImg').addClass('dn');
            $('#bizCertModalFrame').removeClass('dn').attr('src', url);
        } else {
            $('#bizCertModalFrame').addClass('dn');
            $('#bizCertModalImg').removeClass('dn').attr('src', url);
        }
        $('#bizCertModalDownload').attr('href', downloadUrl);
    });
    $(document).on('click', '#bizCertModalClose', function () {
        $('#bizCertModal').addClass('dn').hide();
        $('#bizCertModalFrame').attr('src', 'about:blank');
        $('#bizCertModalImg').attr('src', '');
    });
</script>
<!-- //@formatter:on -->

<div id="bizCertModal" class="biz-cert-modal dn">
    <div class="biz-cert-modal__panel">
        <button type="button" id="bizCertModalClose" style="position:absolute; top:10px; right:10px;">X</button>
        <div style="margin-bottom:10px;">
            <a id="bizCertModalDownload" href="#" class="btn btn-sm btn-gray">다운로드</a>
        </div>
        <div class="biz-cert-modal__viewer">
            <iframe id="bizCertModalFrame" class="dn" frameborder="0"></iframe>
            <img id="bizCertModalImg" class="dn" src="" alt=""/>
        </div>
    </div>
</div>
