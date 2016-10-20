Summary:      Serweb Framework
Name:	      serweb-frmwrk
Version:      1.0.9
Release:      0
License:      GPL
Group:        System Environment/Daemons
Source:       %{name}-%{version}-%{release}.tar.gz
URL:          http://iptel.org/
BuildArch:    noarch
BuildRoot:    /var/tmp/%{name}-%{version}-root
Requires:     php >= 5.0 

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
cp -r TODO.txt INSTALL.txt COPYING CHANGELOG example-app %{buildroot}/usr/share/doc/serweb-frmwrk
mkdir -p %{buildroot}/etc/serweb-frmwrk
mv %{buildroot}/usr/share/serweb-frmwrk/config/* %{buildroot}/etc/serweb-frmwrk/
rmdir %{buildroot}/usr/share/serweb-frmwrk/config
ln -sf /etc/serweb-frmwrk %{buildroot}/usr/share/serweb-frmwrk/config

%clean
rm -rf %{buildroot}


%post

mkdir -p /var/lib/serweb-frmwrk
chown apache:apache /var/lib/serweb-frmwrk
chmod 755 /var/lib/serweb-frmwrk
        
%postun

rm -rf /var/lib/serweb-frmwrk        

        
%files
%defattr(-,root,root)
/usr/share/serweb-frmwrk/*
/usr/share/doc/serweb-frmwrk/*
%config /etc/serweb-frmwrk/*

%changelog

* Mon Jul 29 2013 Karel Kozlik <karel@iptel.org>
- removing dependency on runkit

* Fri Sep 9 2011 Karel Kozlik <karel@iptel.org>
- fixing problem with creating/removing symlink to config directory during upgrade

* Thu Apr 21 2011 Pavel Kasparek <pavel@iptel.org>
- initial rpm spec version

