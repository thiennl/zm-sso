Name:           zm-sso
Version:        1.0.0
Release:        1%{?dist}
Summary:        Zimbra Single Sign On (Zm SSO)

Group:          Applications/Internet
License:        AGPLv3
URL:            https://gitlab.com/iway/zm-sso
Source0:        https://gitlab.com/iway/zm-sso/-/archive/%{version}/zm-sso-%{version}.tar.gz

Requires:       zimbra-store >= 8.8
BuildRequires:  java-1.8.0-openjdk-devel maven
BuildArch:      noarch

%description
Zm SSO is the Zimbra Collaboration Open Source Edition extension for single sign-on authentication to the Zimbra Web Client.

%prep
%setup -q

%build
mvn clean package

%install
mkdir -p $RPM_BUILD_ROOT/opt/zimbra/lib/ext/zm-sso
mkdir -p $RPM_BUILD_ROOT/opt/zimbra/conf
cp -R target/*.jar $RPM_BUILD_ROOT/opt/zimbra/lib/ext/zm-sso
cp -R target/dependencies/*.jar $RPM_BUILD_ROOT/opt/zimbra/jetty_base/common/lib
cp -R conf/zm.sso.properties $RPM_BUILD_ROOT/opt/zimbra/conf

%posttrans
su - zimbra -c "zmmailboxdctl restart"
su - zimbra -c "zmprov fc all"

%postun
su - zimbra -c "zmmailboxdctl restart"
su - zimbra -c "zmprov fc all"

%files
/opt/zimbra/lib/ext/zm-sso/*.jar
/opt/zimbra/conf/zm.sso.properties

%changelog
* Wed May 03 2021 Nguyen Van Nguyen <nguyennv1981@gmail.com> - 1.0.0-1
- Initial release 1.0.0 from upstream.
