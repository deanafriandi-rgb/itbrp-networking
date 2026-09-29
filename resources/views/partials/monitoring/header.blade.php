<style>
    .monitoring-public-header{
        height:70px;
        padding:0 22px;
        background:#fff;
        border-bottom:1px solid #e4edf6;
    }

    .monitoring-public-header .public-header-inner{
        width:100%;
        max-width:1500px;
        height:100%;
        margin:0 auto;
        display:grid;
        grid-template-columns:auto 1fr auto;
        align-items:center;
        gap:20px;
    }

    .monitoring-public-header .brand{
        display:flex;
        align-items:center;
        gap:10px;
        color:#132f61;
        text-decoration:none;
    }

    .monitoring-public-header .brand img{
        width:40px;
        height:40px;
    }

    .monitoring-public-header .brand strong{
        display:block;
        font-size:20px;
        line-height:1;
    }

    .monitoring-public-header .brand small{
        display:block;
        margin-top:4px;
        color:#7a8aa0;
        font-size:10px;
    }

    .monitoring-public-header .public-nav{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        min-width:0;
    }

    .monitoring-public-header .public-nav a{
        min-height:40px;
        padding:0 16px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;
        border-radius:11px;
        color:#294563;
        font-size:11.5px;
        font-weight:800;
        text-decoration:none;
        white-space:nowrap;
        transition:.18s ease;
    }

    .monitoring-public-header .public-nav a:hover,
    .monitoring-public-header .public-nav a.active{
        color:#0877e8;
        background:#eaf4ff;
    }

    .monitoring-public-header .profile-nav{
        display:flex;
        align-items:center;
        gap:8px;
        min-height:44px;
        padding:5px 10px 5px 6px;
        border:1px solid #dfe8f2;
        border-radius:999px;
        color:#284361;
        background:#f8fbff;
        text-decoration:none;
        transition:.18s ease;
    }

    .monitoring-public-header .profile-nav:hover,
    .monitoring-public-header .profile-nav.active{
        border-color:#c8e0fa;
        background:#edf6ff;
    }

    .monitoring-public-header .profile-nav .admin-avatar{
        flex:0 0 auto;
    }

    .monitoring-public-header .profile-copy{
        display:grid;
        line-height:1.05;
    }

    .monitoring-public-header .profile-copy b{
        font-size:10px;
    }

    .monitoring-public-header .profile-copy small{
        margin-top:3px;
        color:#77889e;
        font-size:9px;
    }

    @media(max-width:760px){
        .monitoring-public-header{
            height:auto;
            padding:10px 14px;
        }

        .monitoring-public-header .public-header-inner{
            grid-template-columns:auto auto;
            gap:10px;
        }

        .monitoring-public-header .public-nav{
            grid-column:1/-1;
            grid-row:2;
            justify-content:flex-start;
            overflow-x:auto;
            scrollbar-width:none;
        }

        .monitoring-public-header .public-nav::-webkit-scrollbar{
            display:none;
        }

        .monitoring-public-header .profile-nav{
            justify-self:end;
        }
    }

    @media(max-width:520px){
        .monitoring-public-header .brand small,
        .monitoring-public-header .profile-copy{
            display:none;
        }

        .monitoring-public-header .public-nav a{
            flex:1 0 auto;
            padding:0 13px;
        }
    }
</style>

<header class="public-header monitoring-public-header">

    <div class="public-header-inner">

        <a
            class="brand"
            href="{{ route('monitoring.home') }}"
        >
            <img
                src="{{ asset('assets/img/itbrp-mark.svg') }}"
                alt="ITBRP"
            >

            <div>
                <strong>ITBRP</strong>
                <small>Network Monitoring</small>
            </div>
        </a>


        <nav class="public-nav">

            <a
                class="{{ request()->routeIs('monitoring.home') ? 'active' : '' }}"
                href="{{ route('monitoring.home') }}"
            >
                <span class="nav-ico">◉</span>
                Monitoring
            </a>

            <a
                class="{{ request()->routeIs('monitoring.status') ? 'active' : '' }}"
                href="{{ route('monitoring.status') }}"
            >
                <span class="nav-ico">⌁</span>
                Status
            </a>

        </nav>


        <a
            class="profile-nav {{ request()->routeIs('monitoring.profile') ? 'active' : '' }}"
            href="{{ route('monitoring.profile') }}"
            title="Profil"
        >
            <span class="admin-avatar">DA</span>

            <span class="profile-copy">
                <b>Dian Afriandi</b>
                <small>Profil</small>
            </span>
        </a>

    </div>

</header>
