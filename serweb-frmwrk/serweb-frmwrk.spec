Summary:      Serweb Framwork
Name:	      serweb-frmwrk
Version:      1.0.0
Release:      1
Copyright:    GPL
Group:        System Environment/Daemons
Source:       %{name}-%{version}-%{release}.tar.gz
URL:          http://iptel.org/
BuildRoot:    /var/tmp/%{name}-%{version}-root
Requires:     php >= 5.0

%description
Serweb Framework

%prep

%setup

%build

%install
rm -rf %{buildroot}
mkdir -p %{buildroot}/usr/share/serweb-frmwrk
cp -r serweb-frmwrk/* %{buildroot}/usr/share/serweb-frmwrk
mkdir -p %{buildroot}/usr/share/doc/serweb-frmwrk
cp TODO.txt INSTALL.txt COPYING %{buildroot}/usr/share/doc/serweb-frmwrk

%clean
rm -rf %{buildroot}

%post
echo "Creating serweb-frmwrk symlinks"
ln -s /etc/serweb-frmwrk /usr/share/serweb-frmwrk/config

%postun
echo "Removing serweb-frmwrk symlinks"
rm -f /usr/share/serweb-frmwrk/config

%files
%defattr(-,root,root)
/usr/share/serweb-frmwrk/*
/usr/share/doc/serweb-frmwrk/*

%changelog

* Thu Apr 21 2011 Pavel Kasparek <pavel@iptel.org>
- initial rpm spec version

