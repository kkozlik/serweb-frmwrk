Summary:      Serweb Framework
Name:	      serweb-frmwrk
Version:      1.0.0
Release:      15
License:      GPL
Group:        System Environment/Daemons
Source:       %{name}-%{version}-%{release}.tar.gz
URL:          http://iptel.org/
BuildArch:    noarch
BuildRoot:    /var/tmp/%{name}-%{version}-root
Requires:     php >= 5.0 php-pecl-runkit

%description
Framework for making very customizable web applications.

%prep

%setup

%build

%install
rm -rf %{buildroot}
mkdir -p %{buildroot}/usr/share/serweb-frmwrk
cp -r serweb-frmwrk/* %{buildroot}/usr/share/serweb-frmwrk
mkdir -p %{buildroot}/usr/share/doc/serweb-frmwrk
cp -r TODO.txt INSTALL.txt COPYING example-app %{buildroot}/usr/share/doc/serweb-frmwrk

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

