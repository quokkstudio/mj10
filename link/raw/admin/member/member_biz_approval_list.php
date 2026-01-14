<!-- //@formatter:off -->
<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchBase" method="get" class="js-form-enter-submit">
    <input type="hidden" name="pageNum" value="<?= Request::get()->get('pageNum', 10) ?>"/>
    <input type="hidden" name="searchFl" value="y"/>

    <div class="table-title gd-help-manual">사업자 가입 승인/관리 검색</div>
    <div class="search-detail-box form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>가입일</th>
                <td>
                    <div class="input-group js-datepicker">
                        <input type="text" name="entryDt[]" value="<?= gd_isset($search['entryDt'][0]); ?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar"></span>
                        </span>
                    </div>
                    <div class="input-group js-datepicker">
                        <input type="text" name="entryDt[]" value="<?= gd_isset($search['entryDt'][1]); ?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar"></span>
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>승인상태</th>
                <td>
                    <?= gd_select_box('approvalStatus', 'approvalStatus', $approvalStatusOptions, null, gd_isset($search['approvalStatus'])); ?>
                </td>
            </tr>
            <tr>
                <th>등록증/사진</th>
                <td>
                    <label class="m-r-10">등록증</label>
                    <?= gd_select_box('certFl', 'certFl', $certOptions, null, gd_isset($search['certFl'])); ?>
                    <label class="m-l-10 m-r-10">적재사진</label>
                    <?= gd_select_box('photoFl', 'photoFl', $photoOptions, null, gd_isset($search['photoFl'])); ?>
                </td>
            </tr>
            <tr>
                <th>검색어</th>
                <td>
                    <?= gd_select_box('key', 'key', $searchKeys, null, gd_isset($search['key'])); ?>
                    <input type="text" name="keyword" value="<?= gd_isset($search['keyword']); ?>" class="width-xl"/>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
    </div>
</form>

<div class="table-header">
    <div class="pull-left">
        <?= gd_display_search_result($page->recode['total'], $page->recode['amount'], '명'); ?>
    </div>
    <div class="pull-right">
        <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
        <button type="button" class="btn btn-white" id="btnExcel">엑셀 다운로드</button>
    </div>
</div>

<form id="frmList" action="" method="get">
    <div class="form-inline">
        <table class="table table-rows">
            <colgroup>
                <col class="width-xs"/>
                <col class="width-xs"/>
                <col class="width-sm"/>
                <col class="width-sm"/>
                <col class="width-md"/>
                <col class="width-md"/>
                <col class="width-md"/>
                <col class="width-md"/>
                <col class="width-sm"/>
                <col class="width-xs"/>
                <col class="width-sm"/>
                <col class="width-sm"/>
                <col class="width-md"/>
                <col class="width-md"/>
            </colgroup>
            <thead>
            <tr>
                <th>
                    <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/>
                </th>
                <th>회원번호</th>
                <th>아이디</th>
                <th>이름</th>
                <th>이메일</th>
                <th>가입일</th>
                <th>업체명</th>
                <th>발주담당자/연락처</th>
                <th>결제담당자/연락처</th>
                <th>등록증</th>
                <th>적재사진</th>
                <th>승인상태</th>
                <th>처리일시</th>
                <th>주소</th>
                <th>액션</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($data)) { ?>
                <?php foreach ($data as $val) { ?>
                    <?php
                    $companyName = gd_isset($val['companyName'], $val['memberCompany']);
                    $status = gd_isset($val['approvalStatus'], 'pending');
                    $statusLabel = ($status === 'approved') ? '승인' : (($status === 'rejected') ? '반려' : '승인대기');
                    $treatAt = $val['approvedAt'] ? $val['approvedAt'] : $val['rejectedAt'];
                    $certLabel = (gd_isset($val['certExists']) === 'y') ? '등록' : '미등록';
                    $addressText = trim(sprintf(
                        '(%s) %s %s',
                        gd_isset($val['zonecode']),
                        gd_isset($val['address']),
                        gd_isset($val['addressSub'])
                    ));
                    ?>
                    <tr class="center">
                        <td>
                            <input type="checkbox" name="chk[]" value="<?= $val['memNo']; ?>"/>
                        </td>
                        <td class="font-num"><?= (int)gd_isset($val['memNo']); ?></td>
                        <td><?= gd_htmlspecialchars($val['memId']); ?></td>
                        <td><?= gd_htmlspecialchars($val['memNm']); ?></td>
                        <td><?= gd_htmlspecialchars($val['email']); ?></td>
                        <td class="font-date"><?= gd_isset($val['entryDt']); ?></td>
                        <td><?= gd_htmlspecialchars($companyName); ?></td>
                        <td><?= gd_htmlspecialchars($val['orderManagerName']); ?><br><?= gd_htmlspecialchars($val['orderManagerPhone']); ?></td>
                        <td><?= gd_htmlspecialchars($val['payManagerName']); ?><br><?= gd_htmlspecialchars($val['payManagerPhone']); ?></td>
                        <td><?= $certLabel; ?></td>
                        <td><?= (int)gd_isset($val['photoCount']); ?></td>
                        <td><?= $statusLabel; ?></td>
                        <td><?= gd_isset($treatAt); ?></td>
                        <td class="left"><?= gd_htmlspecialchars($addressText); ?></td>
                        <td>
                            <a href="./member_biz_approval_view.php?memNo=<?= $val['memNo']; ?>" class="btn btn-xs btn-gray">상세</a>
                            <button type="button" class="btn btn-xs btn-white js-approve" data-mem-no="<?= $val['memNo']; ?>">승인</button>
                            <button type="button" class="btn btn-xs btn-white js-reject" data-mem-no="<?= $val['memNo']; ?>">반려</button>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr class="center">
                    <td colspan="15" class="no-data">검색된 정보가 없습니다.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white" id="btnBatchApprove">선택 승인</button>
            <button type="button" class="btn btn-white" id="btnBatchReject">선택 반려</button>
        </div>
    </div>

    <div class="center"><?= $page->getPage(); ?></div>
</form>

<form id="frmAction" action="../member/member_biz_approval_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value=""/>
    <input type="hidden" name="rejectReason" value=""/>
    <input type="hidden" name="returnUrl" value="<?= urlencode(Request::getQueryString()); ?>"/>
</form>

<form id="frmExcel" action="../member/member_biz_approval_excel.php" method="get">
    <input type="hidden" name="searchFl" value="y"/>
    <?php if (!empty($search['entryDt'][0])) { ?>
        <input type="hidden" name="entryDt[]" value="<?= $search['entryDt'][0]; ?>"/>
    <?php } ?>
    <?php if (!empty($search['entryDt'][1])) { ?>
        <input type="hidden" name="entryDt[]" value="<?= $search['entryDt'][1]; ?>"/>
    <?php } ?>
    <input type="hidden" name="approvalStatus" value="<?= gd_isset($search['approvalStatus']); ?>"/>
    <input type="hidden" name="certFl" value="<?= gd_isset($search['certFl']); ?>"/>
    <input type="hidden" name="photoFl" value="<?= gd_isset($search['photoFl']); ?>"/>
    <input type="hidden" name="key" value="<?= gd_isset($search['key']); ?>"/>
    <input type="hidden" name="keyword" value="<?= gd_isset($search['keyword']); ?>"/>
</form>

<script type="text/javascript">
    function submitAction(mode, memNos, reason) {
        if (!memNos.length) {
            dialog_alert('선택된 회원이 없습니다.');
            return;
        }
        var $form = $('#frmAction');
        $form.find('input[name="mode"]').val(mode);
        $form.find('input[name="rejectReason"]').val(reason || '');
        $form.find('input[name="memNo[]"]').remove();
        memNos.forEach(function (no) {
            $('<input>').attr({type: 'hidden', name: 'memNo[]', value: no}).appendTo($form);
        });
        $form.submit();
    }

    function getSelected() {
        var memNos = [];
        $('input[name="chk[]"]:checked').each(function () {
            memNos.push($(this).val());
        });
        return memNos;
    }

    $(document).on('click', '.js-approve', function () {
        var memNo = $(this).data('mem-no');
        dialog_confirm('선택 회원을 승인 처리하시겠습니까?', function (result) {
            if (result) {
                submitAction('approve', [memNo]);
            }
        });
    });

    $(document).on('click', '.js-reject', function () {
        var memNo = $(this).data('mem-no');
        var reason = prompt('반려 사유를 입력하세요.');
        if (reason === null) {
            return;
        }
        submitAction('reject', [memNo], reason);
    });

    $('#btnBatchApprove').click(function () {
        var memNos = getSelected();
        dialog_confirm('선택 회원을 승인 처리하시겠습니까?', function (result) {
            if (result) {
                submitAction('approve', memNos);
            }
        });
    });

    $('#btnBatchReject').click(function () {
        var memNos = getSelected();
        var reason = prompt('반려 사유를 입력하세요.');
        if (reason === null) {
            return;
        }
        submitAction('reject', memNos, reason);
    });

    $('#btnExcel').click(function () {
        $('#frmExcel').submit();
    });
</script>
<!-- //@formatter:on -->
