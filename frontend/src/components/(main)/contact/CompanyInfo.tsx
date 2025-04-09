const CompanyInfo = () => {
  return (
    <div className="flex flex-col lg:flex-row items-center mx-4 mb-8">
      <div className="w-full lg:w-1/2 text-center p-3">
        <h2 className="text-xl sm:text-2xl font-semibold mb-2">
          A cég székhelye:
        </h2>
        <h3 className="text-lg sm:text-xl font-medium">
          1151 Budapest, Esthajnal utca 3.
        </h3>
      </div>
      <div className="w-full lg:w-1/2 flex justify-center items-center p-3 h-[450px]">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2691.7869668755557!2d19.122489687804872!3d47.57193319689367!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4741da617a64273b%3A0xaccd13e08a0cf041!2sBudapest%2C%20Esthajnal%20u.%203%2C%201151!5e0!3m2!1shu!2shu!4v1683983082405!5m2!1shu!2shu"
          width="100%"
          height="100%"
          style={{ border: 0 }}
          allowFullScreen={true}
          loading="lazy"
          referrerPolicy="no-referrer-when-downgrade"
          className="rounded-lg shadow-md"
        ></iframe>
      </div>
    </div>
  );
};

export default CompanyInfo;
