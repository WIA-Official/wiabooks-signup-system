# WIABooks 회원가입 시스템

위아북스(WIABooks)를 위한 워드프레스 회원가입 및 책 관리 플러그인입니다.

## 📋 개요

WIABooks 회원가입 시스템은 작가들이 자신의 책을 작성하고 관리할 수 있는 통합 플랫폼을 제공합니다. Nextend Social Login을 통한 간편한 구글 로그인, 자동 카테고리 생성, 작가별 권한 관리 등의 기능을 포함합니다.

## ✨ 주요 기능

### 1. 회원가입 플로우
- ✅ Nextend Social Login을 통한 구글 로그인
- ✅ 로그인 후 자동으로 책 정보 입력 페이지로 리다이렉트
- ✅ 책 제목, 소제목, 소개 입력
- ✅ 완료 후 작가 대시보드로 이동

### 2. 자동 카테고리 생성
- ✅ 입력한 책 제목으로 워드프레스 카테고리 자동 생성
- ✅ 한글 → 영문 자동 변환 (Romanization)
- ✅ 중복 방지를 위한 고유 슬러그 생성
- ✅ 책 소개를 카테고리 설명으로 자동 입력

**예시:**
- 책 제목: "나의 첫 소설"
- 카테고리명: 나의 첫 소설
- 슬러그: `naui-cheot-soseol`

### 3. 권한 관리

#### 최고관리자 (Administrator)
- 모든 카테고리 접근 가능
- 모든 게시글 생성/수정/삭제
- 플러그인 설정 관리
- 사용자 관리

#### 작가 (Author)
- 자신이 생성한 카테고리에만 글 작성/수정/삭제
- 다른 작가의 카테고리는 읽기만 가능
- 자기 프로필 수정 가능
- 작성 통계 및 진행률 확인

### 4. 작가 대시보드
- 📊 작성 통계 (총 게시글, 발행된 글, 초안, 단어 수)
- 💰 크레딧 잔액 표시
- 📈 작성 진행률 (목표: 50,000 단어)
- 📝 최근 게시글 목록
- ⚡ 빠른 작업 링크 (새 글 작성, 내 글 관리 등)

### 5. 관리자 기능
- 작가별 책 목록 및 통계
- 크레딧 사용량 모니터링
- 전체 통계 대시보드
- 사용자 관리 (책 제목, 카테고리, 크레딧 표시)

### 6. GeneratePress 테마 연동
- 네비게이션 메뉴에 "내 책 관리" 링크 추가
- 작가 대시보드 위젯
- 커스텀 스타일링
- 작성 진행률 표시

## 🔧 설치 방법

### 요구사항
- WordPress 6.0 이상
- PHP 7.4 이상
- Nextend Social Login 플러그인
- GeneratePress 테마 (권장)

### 설치 단계

1. **플러그인 업로드**
   ```bash
   # FTP나 SSH를 통해 플러그인 디렉토리에 업로드
   /var/www/wiabooks/wp-content/plugins/wiabooks-signup-system/
   ```

2. **플러그인 활성화**
   - 워드프레스 관리자 > 플러그인 > 설치된 플러그인
   - "WIABooks Signup System" 활성화

3. **Nextend Social Login 설정**
   - Nextend Social Login 플러그인 설치 및 활성화
   - 구글 OAuth 2.0 설정
   - 클라이언트 ID 및 시크릿 키 입력

4. **GeneratePress 연동 (선택)**
   - `generatepress-functions.php` 파일의 코드를 복사
   - GeneratePress 자식 테마의 `functions.php`에 붙여넣기
   - 또는 GeneratePress Elements를 사용하여 추가

5. **Permalink 설정**
   - 설정 > 고유주소
   - "변경사항 저장" 클릭 (Rewrite rules 갱신)

## 📁 파일 구조

```
wiabooks-signup-system/
├── wiabooks-signup-system.php    # 메인 플러그인 파일
├── includes/                       # 핵심 클래스 파일
│   ├── class-signup-handler.php
│   ├── class-category-manager.php
│   ├── class-permission-manager.php
│   └── class-admin-dashboard.php
├── admin/                          # 관리자 페이지
│   ├── admin-page.php
│   └── admin-style.css
├── templates/                      # 프론트엔드 템플릿
│   ├── signup-form.php
│   └── author-dashboard.php
├── assets/                         # CSS 및 JS
│   ├── css/
│   │   └── frontend.css
│   └── js/
│       ├── frontend.js
│       └── admin.js
├── generatepress-functions.php     # GeneratePress 연동 코드
└── README.md
```

## 🚀 사용 방법

### 작가 회원가입

1. "회원가입" 버튼 클릭
2. 구글 계정으로 로그인
3. 책 정보 입력
   - 책 제목 (필수)
   - 책 소제목 (선택)
   - 책 소개 (선택)
4. "시작하기" 버튼 클릭
5. 자동으로 작가 대시보드로 이동

### 작가 대시보드 접근

- URL: `https://your-site.com/author-dashboard/`
- 또는 네비게이션 메뉴의 "내 책 관리" 클릭

### 글 작성

1. 작가 대시보드에서 "새 글 작성" 클릭
2. 글 작성 (카테고리는 자동으로 할당됨)
3. 발행 또는 초안 저장

### 관리자 대시보드

- WordPress 관리자 > WIABooks
- 작가 목록, 통계, 설정 확인

## ⚙️ 설정

### 플러그인 설정
- WordPress 관리자 > WIABooks > 설정

**설정 옵션:**
- 기본 크레딧: 신규 가입자에게 제공되는 크레딧 금액 (기본: $1,000)
- 크레딧 시스템 활성화: 향후 PDF/EPUB 생성 시 사용
- 가입 후 리다이렉트: 작가 대시보드 또는 홈페이지

## 🔐 보안

- ✅ Nonce 검증
- ✅ Sanitize 및 Escape 처리
- ✅ SQL Injection 방지
- ✅ 권한 체크
- ✅ CSRF 보호

## 📊 데이터베이스

### 사용자 메타데이터 (wp_usermeta)

| Meta Key | 설명 | 타입 |
|----------|------|------|
| `wiabooks_book_category_id` | 카테고리 ID | int |
| `wiabooks_book_title` | 책 제목 | string |
| `wiabooks_book_subtitle` | 책 소제목 | string |
| `wiabooks_credit` | 크레딧 잔액 | int |
| `wiabooks_signup_date` | 가입일 | datetime |

## 🎨 커스터마이징

### CSS 커스터마이징
- `/assets/css/frontend.css` 수정
- 또는 GeneratePress Customizer 사용

### 템플릿 커스터마이징
- `/templates/` 디렉토리의 파일 수정
- 테마 디렉토리에 오버라이드 템플릿 생성 가능

## 🔄 향후 개발 계획

- [ ] PDF/EPUB 생성 기능 (크레딧 차감)
- [ ] 책 표지 업로드
- [ ] 챕터 관리 시스템
- [ ] 작가 간 협업 기능
- [ ] 출판 상태 관리
- [ ] 이메일 알림

## 🐛 문제 해결

### 404 에러 발생
- 설정 > 고유주소에서 "변경사항 저장" 클릭

### 카테고리가 생성되지 않음
- 플러그인 비활성화 후 재활성화
- PHP 에러 로그 확인

### 권한 문제
- 사용자 역할이 "Author"인지 확인
- 플러그인 재활성화로 권한 재설정

## 📝 라이선스

GPL v2 or later

## 👨‍💻 개발자

WIA Official

## 📞 지원

문제가 발생하면 GitHub Issues에 보고해주세요.

## 🙏 감사

- WordPress
- Nextend Social Login
- GeneratePress

---

**버전:** 1.0.0
**최종 업데이트:** 2025-11-23
