(function () {
  const YOUTUBE_LIST = [
    {
      "title": "명장10 시그니처ㅣ호두 크랜베리🥖",
      "href": "https://www.youtube.com/watch?v=f5qRCcskfZQ",
      "thumb": "https://i.ytimg.com/vi/f5qRCcskfZQ/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLBo0mnIClRXUPlG5s2k59e_rlKGjw"
    },
    {
      "title": "명장10 고구마듬뿍빵",
      "href": "https://www.youtube.com/watch?v=Sgt67RfM-Xc",
      "thumb": "https://i.ytimg.com/vi/Sgt67RfM-Xc/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLDeS_4fJ9_IgRe3P_vvXonCh5_tiQ"
    },
    {
      "title": "명장10 시그니처ㅣ명란 대파빵",
      "href": "https://www.youtube.com/watch?v=GGoryQRkgdI",
      "thumb": "https://i.ytimg.com/vi/GGoryQRkgdI/hqdefault.jpg"
    },
    {
      "title": "명장10 시그니처ㅣ바게트 샌드위치 Baguette Sandwich🥖🥪",
      "href": "https://www.youtube.com/watch?v=zCKWb4i82aI",
      "thumb": "https://i.ytimg.com/vi/zCKWb4i82aI/hqdefault.jpg"
    },
    {
      "title": "명장10 시그니처ㅣ깜파뉴 campagne🥖",
      "href": "https://www.youtube.com/watch?v=PWWoYNRO_g0",
      "thumb": "https://i.ytimg.com/vi/PWWoYNRO_g0/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLAfSbwacwcTp_3t7BGjVKlCQk6d5A"
    },
    {
      "title": "명장10 시그니처ㅣ수플레 치즈 케이크🧀",
      "href": "https://www.youtube.com/watch?v=vACY9kvdHBo",
      "thumb": "https://i.ytimg.com/vi/vACY9kvdHBo/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLA8KJg9bma7cajzzmkOFH2jY8xxEQ"
    },
    {
      "title": "명장10 시그니처ㅣ호롱소세지",
      "href": "https://www.youtube.com/watch?v=yGNx-rBO9ao",
      "thumb": "https://i.ytimg.com/vi/yGNx-rBO9ao/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLDd2oGC-k7F6Gjfnyg7Adqy7hr4RA"
    },
    {
      "title": "명장10 시그니처ㅣ바질토마토🌿🍅",
      "href": "https://www.youtube.com/watch?v=8EnJJDlgqLg",
      "thumb": "https://i.ytimg.com/vi/8EnJJDlgqLg/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLAUaoPjj7wZ3fLUiqbh1oN-JGR2Ag"
    },
    {
      "title": "명장10 송영광의 일하는 모습ㅣ일산 베이커리 카페 명장10",
      "href": "https://www.youtube.com/watch?v=BN56Fncb3dU",
      "thumb": "https://i.ytimg.com/vi/BN56Fncb3dU/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLAEJUBQa_he1MMn79OqjzyjHRM0eg"
    },
    {
      "title": "명장10 프리미엄 데니쉬 시트로 만든ㅣ퀸아망 Kouign-amannㅣ2탄!",
      "href": "https://www.youtube.com/watch?v=hX1MQzzYFoU",
      "thumb": "https://i.ytimg.com/vi/hX1MQzzYFoU/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLCuAAmYGpH-Q04Swpl-syciXTz0wA"
    },
    {
      "title": "명장10 프리미엄 데니쉬 시트로 만든ㅣ몽블랑 Mont Blancㅣ1탄!",
      "href": "https://www.youtube.com/watch?v=NI1NJSrvSzI&pp=0gcJCU0KAYcqIYzv",
      "thumb": "https://i.ytimg.com/vi/NI1NJSrvSzI/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLBaRDGX9qTKCubTfW9TJzWfXx02ww"
    },
    {
      "title": "명장10 x 해피해피케이크 ㅣ 해피해피케이크 시그니쳐 쁘띠갸또 초청 세미나 현장 스케치",
      "href": "https://www.youtube.com/watch?v=1bX4xwtPD5c",
      "thumb": "https://i.ytimg.com/vi/1bX4xwtPD5c/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLBffLAnr2GE2CE0NZXTIpfIWAOIPA"
    },
    {
      "title": "대한민국 제과 명장 송영광ㅣ서울호텔관광직업전문학교 인터뷰",
      "href": "https://www.youtube.com/watch?v=7YJnknCjTRc",
      "thumb": "https://i.ytimg.com/vi/7YJnknCjTRc/hqdefault.jpg?sqp=-oaymwEnCNACELwBSFryq4qpAxkIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB&rs=AOn4CLBl9b1BH1G_iCcDzyCngqCpE4d1ww"
    },
    {
      "title": "명장10 명장이 만든 소화가 잘되는 수제 유산균 초코파이",
      "href": "https://www.youtube.com/watch?v=p3glcXCiM5U",
      "thumb": "https://i.ytimg.com/vi/p3glcXCiM5U/hqdefault.jpg?sqp=-oaymwFBCNACELwBSFryq4qpAzMIARUAAIhCGAHYAQHiAQoIGBACGAY4AUAB8AEB-AHICIAC0AWKAgwIABABGGUgUChJMA8=&rs=AOn4CLDSX-wr6rRt1SyP_K4SWp0xo5Uw0g"
    }
  ];

  const SHORTS_LIST = [
    {
      "title": "명장10 연유파이",
      "href": "https://www.youtube.com/shorts/twOFuIoF_is",
      "thumb": "https://i.ytimg.com/vi/twOFuIoF_is/oar2.jpg"
    },
    {
      "title": "명장10 명란 대파빵 #shorts",
      "href": "https://www.youtube.com/shorts/xFTsi6Kri98",
      "thumb": "https://i.ytimg.com/vi/xFTsi6Kri98/oar2.jpg"
    },
    {
      "title": "명장10 루꼴라 잠봉 베이글",
      "href": "https://www.youtube.com/shorts/Me9m_3zs5sk",
      "thumb": "https://i.ytimg.com/vi/Me9m_3zs5sk/oar2.jpg"
    },
    {
      "title": "명장10 고구마듬뿍빵",
      "href": "https://www.youtube.com/shorts/S3G00PrsKvg",
      "thumb": "https://i.ytimg.com/vi/S3G00PrsKvg/oar2.jpg"
    },
    {
      "title": "명장10 바게트 샌드위치 Baguette Sandwich🥖🥪 #shorts",
      "href": "https://www.youtube.com/shorts/pWdiC_WIsqc",
      "thumb": "https://i.ytimg.com/vi/pWdiC_WIsqc/oar2.jpg"
    },
    {
      "title": "명장10 깜파뉴 campagne🥖 #shorts",
      "href": "https://www.youtube.com/shorts/sF6RpgN2Rxg",
      "thumb": "https://i.ytimg.com/vi/sF6RpgN2Rxg/oar2.jpg"
    },
    {
      "title": "명장10 수플레 치즈 케이크🧀 #shorts",
      "href": "https://www.youtube.com/shorts/vaweb9XvASI",
      "thumb": "https://i.ytimg.com/vi/vaweb9XvASI/oar2.jpg"
    },
    {
      "title": "명장10 호롱 소세지 #shorts",
      "href": "https://www.youtube.com/shorts/yycIEEPHAPE",
      "thumb": "https://i.ytimg.com/vi/yycIEEPHAPE/oar2.jpg"
    },
    {
      "title": "명장10 생크림 몽블랑🍓 #shorts",
      "href": "https://www.youtube.com/shorts/awu-g4Ba_Pc",
      "thumb": "https://i.ytimg.com/vi/awu-g4Ba_Pc/oar2.jpg"
    },
    {
      "title": "명장10 레몬 마들렌🍋ㅣ #명장 #빵 #베이커리 #레몬마들렌 #shorts",
      "href": "https://www.youtube.com/shorts/0hmb-BeadcI",
      "thumb": "https://i.ytimg.com/vi/0hmb-BeadcI/oar2.jpg"
    }
  ];

  const listEl = document.getElementById('ytList');
  const pagingEl = document.getElementById('ytPagination');
  const shortsListEl = document.getElementById('ytShortsList');
  const shortsPagingEl = document.getElementById('ytShortsPagination');
  if (!listEl || !shortsListEl) return;

  const perPage = 12;
  const modal = createModal();

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function getVideoId(url) {
    if (!url) return '';
    const watchMatch = url.match(/[?&]v=([^&]+)/);
    if (watchMatch) return watchMatch[1];
    const shortsMatch = url.match(/\/shorts\/([^?]+)/);
    return shortsMatch ? shortsMatch[1] : '';
  }

  function buildThumb(item) {
    if (item.thumb) return item.thumb;
    const id = getVideoId(item.href);
    return id ? `https://i.ytimg.com/vi/${id}/hqdefault.jpg` : '';
  }

  function renderList({ data, list, paging, type }, page) {
    const totalPages = Math.max(1, Math.ceil(data.length / perPage));
    const start = (page - 1) * perPage;
    const items = data.slice(start, start + perPage);

    list.innerHTML = items
      .map((item) => {
        const thumbUrl = buildThumb(item);
        const thumb = thumbUrl
          ? `<img src="${thumbUrl}" alt="${escapeHtml(item.title)}">`
          : '';
        const videoId = getVideoId(item.href);
        return `
          <div class="yt-card" data-video-id="${videoId || ''}" data-type="${type}">
            <div class="yt-thumb">${thumb}</div>
            <div class="yt-body">
              <div class="yt-title">${escapeHtml(item.title)}</div>
            </div>
          </div>
        `;
      })
      .join('');

    const cards = list.querySelectorAll('.yt-card');
    cards.forEach((card, index) => {
      card.style.animationDelay = `${index * 70}ms`;
      requestAnimationFrame(() => card.classList.add('is-in'));
    });

    if (paging) {
      paging.innerHTML = '';
      for (let p = 1; p <= totalPages; p++) {
        const a = document.createElement('a');
        a.href = '#';
        a.className = 'yt-page' + (p === page ? ' is-active' : '');
        a.textContent = String(p);
        a.dataset.page = String(p);
        a.dataset.type = type;
        paging.appendChild(a);
      }
    }
  }

  function createModal() {
    const wrapper = document.createElement('div');
    wrapper.className = 'yt-modal';
    wrapper.innerHTML = `
      <div class="yt-modal-content" role="dialog" aria-modal="true">
        <button type="button" class="yt-modal-close" aria-label="닫기">×</button>
        <iframe class="yt-modal-frame" src="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      </div>
    `;
    document.body.appendChild(wrapper);

    const close = () => {
      wrapper.classList.remove('is-open', 'is-shorts');
      const frame = wrapper.querySelector('.yt-modal-frame');
      if (frame) frame.src = '';
    };
    wrapper.addEventListener('click', (e) => {
      if (e.target === wrapper) close();
    });
    wrapper.querySelector('.yt-modal-close').addEventListener('click', close);
    return { wrapper, close };
  }

  function openModal(id, type) {
    if (!id) return;
    const frame = modal.wrapper.querySelector('.yt-modal-frame');
    frame.src = `https://www.youtube.com/embed/${id}?autoplay=1&rel=0`;
    modal.wrapper.classList.add('is-open');
    if (type === 'shorts') {
      modal.wrapper.classList.add('is-shorts');
    }
  }

  listEl.addEventListener('click', function (e) {
    const card = e.target.closest('.yt-card');
    if (!card) return;
    openModal(card.getAttribute('data-video-id'), card.getAttribute('data-type'));
  });

  shortsListEl.addEventListener('click', function (e) {
    const card = e.target.closest('.yt-card');
    if (!card) return;
    openModal(card.getAttribute('data-video-id'), card.getAttribute('data-type'));
  });

  if (pagingEl) {
    pagingEl.addEventListener('click', function (e) {
      const target = e.target;
      if (target && target.classList.contains('yt-page')) {
        e.preventDefault();
        const page = parseInt(target.dataset.page || '1', 10);
        renderList({ data: YOUTUBE_LIST, list: listEl, paging: pagingEl, type: 'youtube' }, page);
      }
    });
  }

  if (shortsPagingEl) {
    shortsPagingEl.addEventListener('click', function (e) {
      const target = e.target;
      if (target && target.classList.contains('yt-page')) {
        e.preventDefault();
        const page = parseInt(target.dataset.page || '1', 10);
        renderList({ data: SHORTS_LIST, list: shortsListEl, paging: shortsPagingEl, type: 'shorts' }, page);
      }
    });
  }

  const tabs = document.querySelectorAll('.yt-tab');
  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      tabs.forEach((t) => t.classList.remove('is-active'));
      tab.classList.add('is-active');
      const target = tab.getAttribute('data-target');
      document.querySelectorAll('.yt-panel').forEach((panel) => {
        panel.classList.toggle('is-active', panel.getAttribute('data-panel') === target);
      });
    });
  });

  renderList({ data: YOUTUBE_LIST, list: listEl, paging: pagingEl, type: 'youtube' }, 1);
  renderList({ data: SHORTS_LIST, list: shortsListEl, paging: shortsPagingEl, type: 'shorts' }, 1);
})();
